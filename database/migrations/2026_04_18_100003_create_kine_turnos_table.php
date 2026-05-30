<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kine_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kine_profile_id')->constrained('kine_profiles')->cascadeOnDelete();
            $table->foreignId('tratamiento_id')->nullable()->constrained('kine_tratamientos')->nullOnDelete();
            $table->foreignId('professional_id')->nullable()->constrained('professionals')->nullOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->enum('estado', ['pendiente', 'confirmado', 'atendido', 'cancelado', 'ausente'])->default('pendiente');
            $table->string('motivo', 255)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('inicio');
            $table->index('estado');
            $table->index(['professional_id', 'inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kine_turnos');
    }
};
