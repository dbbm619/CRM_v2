<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Factura;
use App\Models\UnpaidFacturaSnapshot;
use App\Mail\UnpaidFacturasSnapshotMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Comando Artisan que crea un "snapshot" diario de facturas no pagadas
 * y envía por correo el detalle si existen registros.
 *
 * Uso: `php artisan snapshots:unpaid-facturas`
 */
class SnapshotUnpaidFacturas extends Command
{
    /**
     * Nombre y firma del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'snapshots:unpaid-facturas';

    /**
     * Descripción del comando: indica la finalidad del snapshot diario.
     *
     * @var string
     */
    protected $description = 'Crea un snapshot diario de facturas no pagadas (estado emitida)';

    /**
     * Execute the console command.
     */
    /**
     * Ejecuta el comando.
     *
     * Pasos:
     * 1) Truncar la tabla de snapshots (se borra el snapshot anterior).
     * 2) Consultar facturas con estado 'emitida' (no pagadas).
     * 3) Insertar cada registro en `unpaid_facturas_snapshots` con datos relevantes.
     * 4) Si hay registros, calcular totales y enviar correo con la información.
     */
    public function handle(): int
    {
        $today = Carbon::now()->toDateString();

        // Remove previous snapshots before creating a new daily snapshot
        // Trunca la tabla para reemplazar el contenido con el snapshot del día.
        // Nota: esto elimina el historial anterior. Si desea conservar historial,
        // no use `truncate()` y guarde el `snapshot_date` como referencia.
        DB::table('unpaid_facturas_snapshots')->truncate();

        $facturas = Factura::with(['cliente', 'venta'])
            ->where('estado', 'emitida')
            ->get();

        $count = 0;

        foreach ($facturas as $factura) {
            // Si faltan relaciones (cliente/venta), se omite la factura
            if (! $factura->cliente || ! $factura->venta) {
                continue; // omitir registros incompletos
            }

            // Como la tabla fue truncada arriba, no es necesario verificar
            // si el registro ya existe para el día de hoy.

            UnpaidFacturaSnapshot::create([
                'factura_id' => $factura->id,
                'numero_factura' => $factura->numero_factura,
                'cliente_nombre' => $factura->cliente->nombre,
                    'factura_fecha' => $factura->fecha_emision ? Carbon::parse($factura->fecha_emision)->toDateString() : null,
                'monto' => $factura->venta->monto,
                'snapshot_date' => $today,
            ]);

            $count++;
        }

        $this->info("Snapshot complete: {$count} unpaid factura(s) recorded for {$today}.");

        // Send email with snapshot data
        // Recupera los snapshots recién insertados para enviarlos por correo
        $snapshots = UnpaidFacturaSnapshot::where('snapshot_date', $today)->get();

        $recipient = env('SNAPSHOT_EMAIL_TO', config('mail.from.address'));
        if ($snapshots->count() === 0) {
            // No hay facturas pendientes; no enviamos correo.
            $this->info('No se encontraron facturas sin pagar. No se envió correo.');
            return Command::SUCCESS;
        }

        if ($recipient) {
            // Calcular totales para incluir en el correo
            $totalCount = $snapshots->count();
            $totalAmount = $snapshots->sum('monto');
            Mail::to($recipient)->send(new UnpaidFacturasSnapshotMail($snapshots, $today, $totalCount, $totalAmount));
            $this->info("Snapshot email sent to: {$recipient}");
        } else {
            $this->warn('No recipient defined for snapshot email. Set SNAPSHOT_EMAIL_TO in .env or configure mail.from.address');
        }

        return Command::SUCCESS;
    }
}
