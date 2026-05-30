<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Notas clínicas y zona específica en sesiones
        Schema::table('este_sesiones', function (Blueprint $table) {
            $table->text('notas_clinicas')->nullable()->after('resultados_observados');
            $table->string('intensidad', 30)->nullable()->after('notas_clinicas');
            $table->string('zona_especifica', 150)->nullable()->after('intensidad');
        });

        // Fotos por sesión / paciente
        Schema::create('este_session_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estetic_profile_id')->constrained('estetic_profiles')->cascadeOnDelete();
            $table->foreignId('sesion_id')->nullable()->constrained('este_sesiones')->nullOnDelete();
            $table->foreignId('tratamiento_id')->nullable()->constrained('este_tratamientos')->nullOnDelete();
            $table->enum('tipo', ['antes', 'durante', 'despues', 'otro'])->default('antes');
            $table->string('path', 255);
            $table->string('caption', 255)->nullable();
            $table->dateTime('tomada_at')->nullable();
            $table->timestamps();

            $table->index(['estetic_profile_id', 'tipo']);
            $table->index('tratamiento_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('este_session_photos');
        Schema::table('este_sesiones', function (Blueprint $table) {
            $table->dropColumn(['notas_clinicas', 'intensidad', 'zona_especifica']);
        });
    }
};
