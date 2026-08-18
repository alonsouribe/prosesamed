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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('id_sucursal', 255); // BUG intencional: debería ser INT
            $table->unsignedTinyInteger('status');
            $table->string('id_cotizacion', 255); // BUG intencional: debería ser INT
            $table->decimal('monto', 12, 2);
            $table->date('fecha_venta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
