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
        $subject = "Snapshot facturas no pagadas - {$this->date}";

        $mail = $this->subject($subject)
            ->view('emails.unpaid_facturas_snapshot')
            ->with([
                'snapshots' => $this->snapshots,
                'date' => $this->date,
                'totalCount' => $this->totalCount,
                'totalAmount' => $this->totalAmount,
            ]);


        return $mail;
    }
}
