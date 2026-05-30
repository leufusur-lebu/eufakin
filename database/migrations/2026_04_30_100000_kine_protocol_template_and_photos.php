<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Plantilla de protocolo en tipos de tratamiento kine
        Schema::table('kine_tipos_tratamientos', function (Blueprint $table) {
            $table->unsignedSmallInteger('sesiones_recomendadas')->default(1)->after('precio_base');
            $table->unsignedSmallInteger('intervalo_dias')->default(7)->after('sesiones_recomendadas');
            $table->text('protocolo')->nullable()->after('intervalo_dias');
            $table->string('color', 16)->nullable()->after('protocolo');
        });

        // Notas clínicas en sesiones kine
        Schema::table('kine_sesiones', function (Blueprint $table) {
            $table->text('notas_clinicas')->nullable()->after('escala_dolor');
            $table->string('rom', 50)->nullable()->after('notas_clinicas');
            $table->string('fuerza_muscular', 30)->nullable()->after('rom');
            $table->unsignedSmallInteger('duracion_real_minutos')->nullable()->after('fuerza_muscular');
        });

        // Fotos / archivos clínicos por sesión kine
        Schema::create('kine_session_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kine_profile_id')->constrained('kine_profiles')->cascadeOnDelete();
            $table->foreignId('sesion_id')->nullable()->constrained('kine_sesiones')->nullOnDelete();
            $table->foreignId('tratamiento_id')->nullable()->constrained('kine_tratamientos')->nullOnDelete();
            $table->enum('tipo', ['inicial', 'evolucion', 'final', 'rx', 'otro'])->default('evolucion');
            $table->string('path', 255);
            $table->string('caption', 255)->nullable();
            $table->dateTime('tomada_at')->nullable();
            $table->timestamps();

            $table->index(['kine_profile_id', 'tipo']);
            $table->index('tratamiento_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kine_session_photos');
        Schema::table('kine_sesiones', function (Blueprint $table) {
            $table->dropColumn(['notas_clinicas', 'rom', 'fuerza_muscular', 'duracion_real_minutos']);
        });
        Schema::table('kine_tipos_tratamientos', function (Blueprint $table) {
            $table->dropColumn(['sesiones_recomendadas', 'intervalo_dias', 'protocolo', 'color']);
        });
    }
};
