<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kine_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tratamiento_id')->constrained('kine_tratamientos')->cascadeOnDelete();
            $table->foreignId('turno_id')->nullable()->constrained('kine_turnos')->nullOnDelete();
            $table->unsignedInteger('numero_sesion');
            $table->date('fecha');
            $table->text('evolucion')->nullable();
            $table->text('ejercicios')->nullable();
            $table->unsignedTinyInteger('escala_dolor')->nullable()->comment('0-10');
            $table->enum('estado', ['programada', 'realizada', 'no_realizada'])->default('programada');
            $table->timestamps();

            $table->index(['tratamiento_id', 'numero_sesion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kine_sesiones');
    }
};
