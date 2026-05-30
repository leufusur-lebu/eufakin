<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('este_tipos_tratamientos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('duracion_minutos');
            $table->decimal('precio_base', 10, 2);
            $table->enum('categoria', ['facial', 'cuerpo', 'masajes', 'depilacion', 'otro'])->default('otro');
            $table->text('materiales_requeridos')->nullable();
            $table->text('contraindicaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
            $table->index('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('este_tipos_tratamientos');
    }
};
