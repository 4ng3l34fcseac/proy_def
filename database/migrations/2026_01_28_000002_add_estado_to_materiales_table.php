<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega la columna 'estado' a la tabla 'materiales' para soportar
     * la baja logica (soft delete manual) en lugar de la eliminacion fisica.
     * Esto preserva el historial de pedidos y movimientos de inventario.
     */
    public function up(): void
    {
        Schema::table('materiales', function (Blueprint $table) {
            $table->string('estado', 20)->default('Activo')->after('medida');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materiales', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropColumn('estado');
        });
    }
};
