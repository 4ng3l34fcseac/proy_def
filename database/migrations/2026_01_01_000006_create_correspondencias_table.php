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
        Schema::create('correspondencias', function (Blueprint $table) {
            $table->id();
            $table->string('numero_documento')->unique(); // CITE o número único
            $table->string('tipo_documento'); // nota_interna, nota_externa, informe, instructivo, memorandum
            $table->string('direccion'); // ingreso, egreso
            $table->date('fecha');
            $table->string('remitente');
            $table->string('destinatario');
            $table->text('asunto');
            $table->string('archivo_adjunto')->nullable(); // ruta del archivo PDF o imagen
            $table->string('estado_atencion')->default('pendiente'); // pendiente, en_proceso, atendido
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondencias');
    }
};
