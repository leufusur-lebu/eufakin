<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('este_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estetic_profile_id')->constrained('estetic_profiles')->cascadeOnDelete();
            $table->foreignId('tratamiento_id')->nullable()->constrained('este_tratamientos')->nullOnDelete();
            $table->foreignId('sesion_id')->nullable()->constrained('este_sesiones')->nullOnDelete();
            $table->date('fecha');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo', ['efectivo', 'transferencia', 'debito', 'credito', 'mercadopago', 'otro'])->default('efectivo');
            $table->enum('estado', ['pendiente', 'pagado', 'anulado'])->default('pagado');
            $table->string('comprobante', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('fecha');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('este_pagos');
    }
};
