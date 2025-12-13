<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crea la tabla para almacenar una captura (snapshot) diaria de las facturas
        // que aún no están pagadas. Esta tabla se trunca antes de cada ejecución
        // del comando para mantener sólo el snapshot reciente.
        Schema::create('unpaid_facturas_snapshots', function (Blueprint $table) {
            $table->id();

            // Identificador de la factura original (clave foránea)
            $table->foreignId('factura_id')
                ->constrained('facturas')
                ->onDelete('cascade');

            // Número de la factura, tal como aparece en la entidad `facturas`.
            $table->string('numero_factura');
            $table->string('cliente_nombre');
            // Monto de la venta asociada a la factura
            $table->decimal('monto', 10, 2);
            $table->date('snapshot_date');

            $table->timestamps();

            // Evita registros duplicados de la misma factura en la misma fecha de snapshot
            $table->unique(['factura_id', 'snapshot_date']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unpaid_facturas_snapshots');
    }
};
