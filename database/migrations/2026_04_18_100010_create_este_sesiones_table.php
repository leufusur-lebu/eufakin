<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('este_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tratamiento_id')->constrained('este_tratamientos')->cascadeOnDelete();
            $table->foreignId('turno_id')->nullable()->constrained('este_turnos')->nullOnDelete();
            $table->unsignedInteger('numero_sesion');
            $table->date('fecha');
            $table->text('productos_utilizados')->nullable();
            $table->text('resultados_observados')->nullable();
            $table->unsignedInteger('duracion_real_minutos')->nullable();
            $table->enum('estado', ['programada', 'realizada', 'no_realizada'])->default('programada');
            $table->timestamps();

            $table->index(['tratamiento_id', 'numero_sesion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('este_sesiones');
    }
};
