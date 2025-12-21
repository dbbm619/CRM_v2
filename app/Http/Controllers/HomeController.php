<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Factura;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IngresosPorMesExport;
use App\Exports\FlujoCajaMensualExport;

class HomeController extends Controller
{
    public function index()
    {


        // 🟦 Lista de clientes para el filtro
        $listaClientes = Cliente::pluck('nombre', 'id');

        // Obtener filtros
        $desde   = request('desde');
        $hasta   = request('hasta');
        $cliente = request('cliente_id');
        $tipo    = request('tipo');
        $periodo = request('periodo');

        //Periodo
        if ($periodo) {
            $hoy = Carbon::today();

            match ($periodo) {
                'year' => [
                    $desde = $hoy->copy()->startOfYear(),
                    $hasta = $hoy
                ],
                'semester' => [
                    $desde = $hoy->month <= 6
                        ? $hoy->copy()->startOfYear()
                        : $hoy->copy()->month(7)->startOfMonth(),
                    $hasta = $hoy
                ],
                'quarter' => [
                    $desde = $hoy->copy()->startOfQuarter(),
                    $hasta = $hoy
                ],
                'four_months' => [
                    $desde = match (true) {
                        $hoy->month <= 4 => $hoy->copy()->month(1)->startOfMonth(),
                        $hoy->month <= 8 => $hoy->copy()->month(5)->startOfMonth(),
                        default => $hoy->copy()->month(9)->startOfMonth(),
                    },
                    $hasta = $hoy
                ],
                default => null
            };
        }


        // 🟦 Base de Clientes (Oportunidades)
        $clientesBase = Cliente::query()
            ->when($cliente, fn($q) => $q->where('id', $cliente))
            ->get();

        // 🟦 Filtrar Ventas No Canceladas (validas)
        $ventasValidas = Venta::query()
            ->where('estado', '!=', 'cancelada')
            ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
            ->get()
            ->groupBy('cliente_id');

        // 🟦 Calcular Clientes Activos, OneShot y Recurrentes
        $clientesActivos = $clientesBase->filter(function ($cliente) use ($ventasValidas) {
            return isset($ventasValidas[$cliente->id]);
        });

        $clientesOneShot = $clientesBase->filter(function ($cliente) use ($ventasValidas) {
            return isset($ventasValidas[$cliente->id]) && $ventasValidas[$cliente->id]->count() === 1;
        });

        $clientesRecurrentes = $clientesBase->filter(function ($cliente) use ($ventasValidas) {
            return isset($ventasValidas[$cliente->id]) && $ventasValidas[$cliente->id]->count() > 1;
        });

        // Aplicar filtros por tipo
        $clientesFiltradosPorTipo = match ($tipo) {
            'oneshot'    => $clientesOneShot,
            'recurrente' => $clientesRecurrentes,
            default      => $clientesBase,
        };

        // 🟦 Oportunidades: Clientes con o sin ventas
        $totalOportunidades = Cliente::whereHas('ventas', function ($q) use ($desde, $hasta) {
            if ($desde) {
                $q->whereDate('fecha', '>=', $desde);
            }
            if ($hasta) {
                $q->whereDate('fecha', '<=', $hasta);
            }
        })
            ->when($cliente, fn($q) => $q->where('id', $cliente))
            ->when($tipo === 'recurrente', fn($q) => $q->has('ventas', '>', 1))
            ->when($tipo === 'oneshot', fn($q) => $q->has('ventas', '=', 1))
            ->count();

        // 🟦 Clientes activos: Clientes con ventas no canceladas
        $clientesActivosCount = $clientesActivos->intersect($clientesFiltradosPorTipo)->count();

        // 🟦 OneShot: Clientes con exactamente una venta no cancelada
        $clientesOneShotCount = $clientesOneShot->intersect($clientesFiltradosPorTipo)->count();

        // 🟦 Recurrentes: Clientes con más de una venta no cancelada
        $clientesRecurrentesCount = $clientesRecurrentes->intersect($clientesFiltradosPorTipo)->count();

        // 🟦 Total de ventas y facturas filtradas
        $ventasFiltradas = Venta::whereIn('cliente_id', $clientesFiltradosPorTipo->pluck('id'))
            ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
            ->where('estado', '!=', 'cancelada')
            ->get();

        $facturasFiltradas = Factura::whereIn('cliente_id', $clientesFiltradosPorTipo->pluck('id'))
            ->when($desde, fn($q) => $q->whereDate('fecha_emision', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha_emision', '<=', $hasta))
            ->where('estado', '!=', 'anulada')
            ->get();


        // 🟦 Contadores generales
        $totalVentas   = $ventasFiltradas->count();
        $totalFacturas = $facturasFiltradas->count();

        // 🟦 Total por cobrar (ventas pendientes)
        $cuentaPorCobrar = $ventasFiltradas
            ->where('estado', 'pendiente')
            ->sum('monto');

        // 🟦 Total pérdidas (ventas canceladas)
        $perdidas = Venta::whereIn('cliente_id', $clientesFiltradosPorTipo->pluck('id'))
            ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
            ->where('estado', 'cancelada')
            ->sum('monto');

        // -----------------------
        // 🟦 GRÁFICOS DE PIE
        // -----------------------

        // ESTADO DE VENTAS

        $ventasPorEstado = $ventasFiltradas
            ->groupBy('estado')
            ->map(fn($g) => $g->count());

        $ventasEstadoLabels = $ventasPorEstado->keys();
        $ventasEstadoData = $ventasPorEstado->values();

        // ESTADO DE FACTURAS

        $facturasPorEstado = $facturasFiltradas
            ->groupBy('estado')
            ->map(fn($g) => $g->count());

        $facturasEstadoLabels = $facturasPorEstado->keys();
        $facturasEstadoData = $facturasPorEstado->values();

        // -----------------------
        // 🟦 GRÁFICOS
        // -----------------------

        // 🟦 Determinar fechas de inicio y fin según filtros
        $fechaInicio = $desde ? Carbon::parse($desde)->startOfMonth()
            : ($ventasFiltradas->min(fn($v) => $v->fecha) ? Carbon::parse($ventasFiltradas->min(fn($v) => $v->fecha))->startOfMonth() : now()->subMonths(5));

        $fechaFin = $hasta ? Carbon::parse($hasta)->startOfMonth()
            : ($ventasFiltradas->max(fn($v) => $v->fecha) ? Carbon::parse($ventasFiltradas->max(fn($v) => $v->fecha))->startOfMonth() : now());

        // 🟦 Periodo mensual
        $periodoMeses = CarbonPeriod::create($fechaInicio, '1 month', $fechaFin);

        $ventasPorMes = collect();
        $montosPorMes = collect();
        $flujoCaja = collect();

        foreach ($periodoMeses as $mes) {
            $mesKey = $mes->format('Y-m');

            // Conteo de ventas
            $ventasPorMes[$mesKey] = $ventasFiltradas
                ->where(fn($v) => date('Y-m', strtotime($v->fecha)) === $mesKey)
                ->count();

            // Labels 
            $labelsVentasMes = $ventasPorMes->keys()->map(function ($mes) {
                return Carbon::createFromFormat('Y-m', $mes)
                    ->locale('es')
                    ->translatedFormat('M.y'); // ene.25
            });

            // Montos pagados
            $montosPorMes[$mesKey] = $ventasFiltradas
                ->where('estado', 'pagada')
                ->where(fn($v) => date('Y-m', strtotime($v->fecha)) === $mesKey)
                ->sum('monto');

            // Flujo de caja (igual que montos pagados)
            $flujoCaja[$mesKey] = $montosPorMes[$mesKey];
        }

        // 🟦 Ventas por cliente
        $ventasPorCliente = $ventasFiltradas
            ->groupBy('cliente_id')
            ->map->count();

        $nombresClientes = Cliente::whereIn('id', $ventasPorCliente->keys())
            ->pluck('nombre', 'id');

        // --------------------------
        // 🔹 Embudo de ventas
        // --------------------------

        // 📊 Filtros activos
        $tasaConversion = $totalOportunidades > 0 ? round(($clientesActivosCount / $totalOportunidades) * 100, 2) : 0;

        return view('home', compact(
            'listaClientes',

            'totalVentas',
            'totalFacturas',
            'ventasPorMes',
            'montosPorMes',
            'labelsVentasMes',

            'ventasPorCliente',
            'nombresClientes',
            'cuentaPorCobrar',
            'perdidas',

            'flujoCaja',

            'ventasFiltradas',
            'totalOportunidades',
            'clientesActivosCount',
            'clientesOneShotCount',
            'clientesRecurrentesCount',
            'ventasEstadoLabels',
            'ventasEstadoData',
            'facturasEstadoLabels',
            'facturasEstadoData',
            'tasaConversion',
        ));
    }

    public function eliminados()
    {
        return view('eliminados.index', [
            'clientes' => Cliente::onlyTrashed()->get(),
            'ventas'   => Venta::onlyTrashed()->with('cliente')->get(),
            'facturas' => Factura::onlyTrashed()->with('cliente')->get(),
        ]);
    }

    public function exportIngresosPorMes()
    {
        $ventasQuery = Venta::where('estado', 'pagada');

        // Filtros
        if (request('desde')) {
            $ventasQuery->whereDate('fecha', '>=', request('desde'));
        }

        if (request('hasta')) {
            $ventasQuery->whereDate('fecha', '<=', request('hasta'));
        }

        if (request('cliente_id')) {
            $ventasQuery->where('cliente_id', request('cliente_id'));
        }

        $ventas = $ventasQuery
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as periodo, SUM(monto) as total")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $data = $ventas->map(fn($v) => [
            $v->periodo,
            $v->total
        ])->toArray();


        return Excel::download(
            new IngresosPorMesExport($data),
            'ingresos_totales_por_mes.xlsx'
        );
    }


    public function exportFlujoCajaMensual()
    {
        $ventasQuery = Venta::where('estado', 'pagada');

        // Filtros
        if (request('desde')) {
            $ventasQuery->whereDate('fecha', '>=', request('desde'));
        }

        if (request('hasta')) {
            $ventasQuery->whereDate('fecha', '<=', request('hasta'));
        }

        if (request('cliente_id')) {
            $ventasQuery->where('cliente_id', request('cliente_id'));
        }

        $ventas = $ventasQuery
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as periodo, SUM(monto) as total")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $data = $ventas->map(fn($v) => [
            $v->periodo,
            $v->total
        ])->toArray();

        return Excel::download(
            new FlujoCajaMensualExport($data),
            'flujo_real_caja_mensual.xlsx'
        );
    }
}
