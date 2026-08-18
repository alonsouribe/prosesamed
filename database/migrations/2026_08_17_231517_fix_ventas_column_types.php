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
        Schema::table('ventas', function (Blueprint $table) {
            $table->unsignedInteger('id_sucursal')->change();
            $table->unsignedInteger('id_cotizacion')->change();
            $table->index(['id_sucursal', 'status', 'fecha_venta'], 'idx_ventas_filtro_orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_filtro_orden');
            $table->string('id_sucursal', 255)->change();
            $table->string('id_cotizacion', 255)->change();
        });
    }
};
