<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para representar un snapshot diario de facturas no pagadas.
 *
 * Campos principales:
 * - factura_id: id de la factura original (FK)
 * - numero_factura: número de factura
 * - cliente_nombre: nombre del cliente asociado
 * - monto: monto de la venta asociada
 * - factura_fecha: fecha de emisión de la factura
 * - snapshot_date: fecha en que se creó el snapshot
 */
class UnpaidFacturaSnapshot extends Model
{
    // Tabla asociada
    protected $table = 'unpaid_facturas_snapshots';

    // Atributos que pueden asignarse masivamente
    protected $fillable = [
        'factura_id',
        'numero_factura',
        'cliente_nombre',
        'monto',
        'snapshot_date',
        'factura_fecha',
    ];
}
