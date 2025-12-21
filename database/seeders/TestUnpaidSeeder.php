<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Factura;
use Illuminate\Support\Facades\Hash;

class TestUnpaidSeeder extends Seeder
{
    public function run()
    {
        $cliente = Cliente::create([
            'nombre' => 'Cliente Prueba',
            'rut' => '12345678-9',
            'correo' => 'cliente.prueba@example.test',
        ]);

        $venta = Venta::create([
            'cliente_id' => $cliente->id,
            'monto' => 150000,
            'fecha' => now(),
        ]);

        Factura::create([
            'numero_factura' => 'TEST-001',
            'cliente_id' => $cliente->id,
            'venta_id' => $venta->id,
            'fecha_emision' => now(),
            'estado' => 'emitida',
        ]);
    }
}
