<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'cliente_id',
        'venta_id',
        'numero_factura',
        'fecha_emision',
        'estado',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class)->withTrashed();
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class)->withTrashed();
    }
}

