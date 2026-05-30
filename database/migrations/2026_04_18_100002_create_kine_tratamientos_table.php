<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kine_tratamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kine_profile_id')->constrained('kine_profiles')->cascadeOnDelete();
            $table->foreignId('professional_id')->nullable()->constrained('professionals')->nullOnDelete();
            $table->string('diagnostico', 255);
            $table->text('plan')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->unsignedInteger('sesiones_totales')->default(0);
            $table->unsignedInteger('sesiones_realizadas')->default(0);
            $table->decimal('costo_sesion', 10, 2)->default(0);
            $table->decimal('costo_total', 10, 2)->default(0);
            $table->enum('estado', ['activo', 'finalizado', 'suspendido', 'cancelado'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kine_tratamientos');
    }
};
