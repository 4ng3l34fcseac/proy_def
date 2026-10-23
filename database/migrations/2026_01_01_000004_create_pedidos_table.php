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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('justificacion')->nullable();
            $table->string('estado')->default('pendiente'); // pendiente, autorizado, rechazado, despachado
            $table->text('observaciones')->nullable(); // observaciones de rechazo o comentarios
            $table->dateTime('fecha_entrega')->nullable(); // para cuando se despacha
            $table->string('recibido_por')->nullable(); // persona que recibe el despacho
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
