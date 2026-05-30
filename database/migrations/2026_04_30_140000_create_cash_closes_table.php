<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_closes', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->decimal('total_sistema', 12, 2)->default(0);
            $table->decimal('total_efectivo_sistema', 12, 2)->default(0);
            $table->decimal('total_efectivo_contado', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->default(0);
            $table->json('breakdown_metodos')->nullable();
            $table->json('breakdown_modulos')->nullable();
            $table->unsignedInteger('total_transacciones')->default(0);
            $table->text('observaciones')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at');
            $table->timestamps();

            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closes');
    }
};
