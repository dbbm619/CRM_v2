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
        // Agrega la columna `factura_fecha` para almacenar la fecha de emisión
        // de la factura original. Se hace nullable porque algunas facturas
        // podrían no tener la fecha en el formato esperado.
        Schema::table('unpaid_facturas_snapshots', function (Blueprint $table) {
            $table->date('factura_fecha')->nullable()->after('numero_factura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unpaid_facturas_snapshots', function (Blueprint $table) {
            $table->dropColumn('factura_fecha');
        });
    }
};
