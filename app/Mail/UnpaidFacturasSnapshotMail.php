<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable que representa el correo diario con el snapshot de facturas no pagadas.
 * El contenido se muestra en el cuerpo del correo (HTML) y contiene los totales.
 */
class UnpaidFacturasSnapshotMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Colección de snapshots (registros a incluir en el correo).
     * @var \Illuminate\Support\Collection
     */
    public $snapshots;

    /**
     * Fecha del snapshot (YYYY-MM-DD).
     * @var string
     */
    public $date;

    /**
     * Cantidad de facturas incluidas en el snapshot.
     * @var int
     */
    public $totalCount;

    /**
     * Monto total de las facturas incluidas en el snapshot.
     * @var float
     */
    public $totalAmount;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @param \Illuminate\Support\Collection $snapshots
     * @param string $date
     * @param int $totalCount
     * @param float $totalAmount
     */
    public function __construct($snapshots, $date, $totalCount = 0, $totalAmount = 0)
    {
        $this->snapshots = $snapshots;
        $this->date = $date;
        $this->totalCount = $totalCount;
        $this->totalAmount = $totalAmount;
    }

    /**
     * Construye el mensaje: asigna asunto y la vista que renderiza el contenido.
     */
    public function build()
    {
        $subject = "Informe de facturas sin pagar - {$this->date}";

        // Generar CSV en memoria
        $headers = ['factura_id', 'numero_factura', 'cliente_nombre', 'factura_fecha', 'monto'];
        $handle = fopen('php://temp', 'r+');
        // Escribir cabecera
        fputcsv($handle, $headers);

        foreach ($this->snapshots as $s) {
            fputcsv($handle, [
                $s->factura_id,
                $s->numero_factura,
                $s->cliente_nombre,
                $s->factura_fecha,
                // Asegurar formato numérico con 2 decimales
                number_format($s->monto, 2, '.', ''),
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        // Añadir BOM UTF-8 para compatibilidad con Excel
        $csvWithBom = "\xEF\xBB\xBF" . $csvContent;

        $filename = "informe_facturas_sin_pagar_{$this->date}.csv";

        $mail = $this->subject($subject)
            ->view('emails.unpaid_facturas_snapshot')
            ->with([
                'snapshots' => $this->snapshots,
                'date' => $this->date,
                'totalCount' => $this->totalCount,
                'totalAmount' => $this->totalAmount,
            ])
            ->attachData($csvWithBom, $filename, [
                'mime' => 'text/csv',
            ]);

        return $mail;
    }
}
