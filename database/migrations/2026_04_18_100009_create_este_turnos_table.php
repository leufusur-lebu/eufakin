<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('este_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estetic_profile_id')->constrained('estetic_profiles')->cascadeOnDelete();
            $table->foreignId('tratamiento_id')->nullable()->constrained('este_tratamientos')->nullOnDelete();
            $table->foreignId('professional_id')->nullable()->constrained('professionals')->nullOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->enum('estado', ['pendiente', 'confirmado', 'atendido', 'cancelado', 'ausente'])->default('pendiente');
            $table->string('motivo', 255)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('recordatorio_enviado')->default(false);
            $table->timestamps();

            $table->index('inicio');
            $table->index('estado');
            $table->index(['professional_id', 'inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('este_turnos');
    }
};
