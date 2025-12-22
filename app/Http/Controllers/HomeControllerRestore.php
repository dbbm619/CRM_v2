<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Factura;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HomeControllerRestore extends Controller
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

        $ventasQuery = Venta::query();
        $facturasQuery = Factura::query();
        $clientesQuery = Cliente::query();

        // 🟢 FILTRO POR FECHAS
        if ($desde) {
            $ventasQuery->whereDate('fecha', '>=', $desde);
            $facturasQuery->whereDate('fecha_emision', '>=', $desde);
        }
        if ($hasta) {
            $ventasQuery->whereDate('fecha', '<=', $hasta);
            $facturasQuery->whereDate('fecha_emision', '<=', $hasta);
        }

        // 🟢 FILTRO POR CLIENTE
        if ($cliente) {
            $ventasQuery->where('cliente_id', $cliente);
            $facturasQuery->whereHas('venta', function ($q) use ($cliente) {
                $q->where('cliente_id', $cliente);
            });
            $clientesQuery->where('id', $cliente);
        }

        // 🟢 FILTRO POR TIPO DE CLIENTE (recurrente / one-shot)
        if ($tipo == 'recurrente') {
            $clientesRecurrentes = Venta::selectRaw('cliente_id, COUNT(*) as total')
                ->groupBy('cliente_id')
                ->having('total', '>', 1)
                ->pluck('cliente_id');

            $ventasQuery->whereIn('cliente_id', $clientesRecurrentes);
            $clientesQuery->whereHas('ventas', function ($q) {
                $q->where('estado', '!=', 'cancelada');
            }, '>', 1);
        }

        if ($tipo == 'oneshot') {
            $clientesOneShot = Venta::selectRaw('cliente_id, COUNT(*) as total')
                ->groupBy('cliente_id')
                ->having('total', '=', 1)
                ->pluck('cliente_id');

            $ventasQuery->whereIn('cliente_id', $clientesOneShot);
            $clientesQuery->whereHas('ventas', function ($q) {
                $q->where('estado', '!=', 'cancelada');
            }, '=', 1);
            

        }

        if ($tipo) {
            $facturasQuery->whereHas('venta', function ($q) use ($tipo) {

                $sub = Venta::selectRaw('cliente_id, COUNT(*) as total')
                    ->groupBy('cliente_id');

                if ($tipo === 'recurrente') {
                    $sub->having('total', '>', 1);
                } else {
                    $sub->having('total', '=', 1);
                }

                $q->whereIn('cliente_id', $sub->pluck('cliente_id'));
            });
        }

        // Ejecutar las consultas filtradas
        $clientesFiltrados = $clientesQuery->get();
        $ventasFiltradas = Venta::whereIn('cliente_id', $clientesFiltrados->pluck('id'))
                            ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
                            ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
                            ->where('estado', '!=', 'cancelada')
                            ->get();
        $facturasFiltradas = Factura::whereIn('cliente_id', $clientesFiltrados->pluck('id'))
                            ->when($desde, fn($q) => $q->whereDate('fecha_emision', '>=', $desde))
                            ->when($hasta, fn($q) => $q->whereDate('fecha_emision', '<=', $hasta))
                            ->where('estado', '!=', 'anulada')
                            ->get();
        

        // Contadores filtrados
        $totalClientes = $ventasFiltradas
                        ->pluck('cliente_id')
                        ->unique()
                        ->count();

        $totalVentas   = $ventasFiltradas->count();
        $totalFacturas = $facturasFiltradas->count();

        //Aplica filtro pero no saca ningun tipo de venta ni pendiente ni cancelada (para mostrar en contadores)
        $ventasFiltradasAll = Venta::whereIn('cliente_id', $clientesFiltrados->pluck('id'))
                            ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
                            ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
                            ->get();

        // Total por cobrar (ventas pendientes)
        $cuentaPorCobrar = $ventasFiltradasAll
            ->where('estado', 'pendiente')
            ->sum('monto');

        // Total pérdidas (ventas canceladas)
        $perdidas = $ventasFiltradasAll
            ->where('estado', 'cancelada')
            ->sum('monto');

        // -----------------------
        // 🟥 GRÁFICOS
        // -----------------------

        // Determinar fechas de inicio y fin según filtros
        $fechaInicio = $desde ? Carbon::parse($desde)->startOfMonth() 
                            : ($ventasFiltradas->min(fn($v) => $v->fecha) ? Carbon::parse($ventasFiltradas->min(fn($v) => $v->fecha))->startOfMonth() : now()->subMonths(5));

        $fechaFin = $hasta ? Carbon::parse($hasta)->startOfMonth() 
                        : ($ventasFiltradas->max(fn($v) => $v->fecha) ? Carbon::parse($ventasFiltradas->max(fn($v) => $v->fecha))->startOfMonth() : now());
            
        // Periodo mensual
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

            // Montos pagados
            $montosPorMes[$mesKey] = $ventasFiltradas
                ->where('estado', 'pagada')
                ->where(fn($v) => date('Y-m', strtotime($v->fecha)) === $mesKey)
                ->sum('monto');

            // Flujo de caja (igual que montos pagados)
            $flujoCaja[$mesKey] = $montosPorMes[$mesKey];
        }

        // 🟦 Ventas por mes
      

        $labelsMeses = $ventasPorMes->keys()->map(function ($mes) {
            return Carbon::createFromFormat('Y-m', $mes)
                ->locale('es')
                ->translatedFormat('M.y'); // ene.25
        });

        // 🟦 Montos por mes
        // Montos por mes (solo ventas pagadas)
      

        $labelsMesesMonto = $montosPorMes->keys()->map(function ($mes) {
            return Carbon::createFromFormat('Y-m', $mes)
                ->locale('es')
                ->translatedFormat('M.y'); // ene.25
        });

        // =======================================
        // NUEVO GRÁFICO: ESTADO DE VENTAS
        // =======================================
        $ventasEstado = $ventasFiltradas
            ->groupBy('estado')
            ->map->count();

        // =======================================
        // NUEVO GRÁFICO: FLUJO REAL DE CAJA
        // (solo montos pagados por mes)
        // =======================================
      

        $labelsMesesFlujo = $flujoCaja->keys()->map(function ($mes) {
            return Carbon::createFromFormat('Y-m', $mes)
                ->locale('es')
                ->translatedFormat('M.y'); // ene.25
        });


        // 🟦 Estado facturas
        $facturasEstado = $facturasFiltradas
            ->groupBy('estado')
            ->map->count();

        // 🟦 Ventas por cliente
        $ventasPorCliente = $ventasFiltradas
            ->groupBy('cliente_id')
            ->map->count();

        $nombresClientes = Cliente::whereIn('id', $ventasPorCliente->keys())
            ->pluck('nombre', 'id');


        // 🔹 Embudo de ventas

        // Filtro por cliente específico
        if ($cliente) {
            $clientesQuery->where('id', $cliente);
        }

        // Obtener clientes filtrados

        $clientesFiltrados = $clientesQuery->get();

$totalOportunidades = Cliente::whereHas('ventas', function ($query) use ($desde, $hasta) {
        if ($desde) {
            $query->whereDate('fecha', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('fecha', '<=', $hasta);
        }

        $query->where('estado', '!=', 'cancelada');
        })
        ->when($cliente, function ($q) use ($cliente) {
            $q->where('id', $cliente);
        })
        ->when($tipo === 'recurrente', function ($q) {
            $q->has('ventas', '>', 1);
        })
        ->when($tipo === 'oneshot', function ($q) {
            $q->has('ventas', '=', 1);
        })
        ->count();
                            
        $clientesActivos    = $clientesFiltrados->filter(function ($c) use ($desde, $hasta) {
                                return $c->ventas
                                    ->where('estado', '!=', 'cancelada')
                                    ->when($desde, fn($ventas) => $ventas->where('fecha', '>=', $desde))
                                    ->when($hasta, fn($ventas) => $ventas->where('fecha', '<=', $hasta))
                                    ->count() > 0;
                            })->count();
        $clientesOneShot    = $clientesFiltrados->filter(function ($c) use ($desde, $hasta) {
                                $ventasNoCanceladas = $c->ventas
                                    ->where('estado', '!=', 'cancelada')
                                    ->when($desde, fn($ventas) => $ventas->where('fecha', '>=', $desde))
                                    ->when($hasta, fn($ventas) => $ventas->where('fecha', '<=', $hasta));

                                return $ventasNoCanceladas->count() === 1;
                            })->count();
        $clientesRecurrentes = $clientesFiltrados->filter(function ($c) use ($desde, $hasta) {
                                $ventasNoCanceladas = $c->ventas
                                    ->where('estado', '!=', 'cancelada')
                                    ->when($desde, fn($ventas) => $ventas->where('fecha', '>=', $desde))
                                    ->when($hasta, fn($ventas) => $ventas->where('fecha', '<=', $hasta));

                                return $ventasNoCanceladas->count() > 1;
                            })->count();
        $ventasNoCanceladas  = $ventasFiltradas->where('estado', '!=', 'cancelada')->count();

        $tasaConversion = $totalOportunidades > 0 ? round(($clientesActivos / $totalOportunidades) * 100, 2) : 0;


        return view('home', compact(
            'listaClientes',
            'totalClientes',
            'totalVentas',
            'totalFacturas',
            'ventasPorMes',
            'montosPorMes',
            'facturasEstado',
            'ventasPorCliente',
            'nombresClientes',
            'cuentaPorCobrar',
            'perdidas',
            'ventasEstado',
            'flujoCaja',
            'labelsMeses',
            'labelsMesesMonto',
            'labelsMesesFlujo',
            'ventasFiltradas',
            'totalOportunidades',
            'clientesActivos',
            'clientesOneShot',
            'clientesRecurrentes',
            'ventasNoCanceladas',
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

    
}
