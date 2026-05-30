<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('este_tipos_tratamientos', function (Blueprint $table) {
            $table->unsignedSmallInteger('sesiones_recomendadas')->default(1)->after('precio_base');
            $table->unsignedSmallInteger('intervalo_dias')->default(7)->after('sesiones_recomendadas');
            $table->text('protocolo')->nullable()->after('intervalo_dias');
            $table->string('imagen_url', 255)->nullable()->after('protocolo');
            $table->string('color', 16)->nullable()->after('imagen_url');
        });
    }

    public function down(): void
    {
        Schema::table('este_tipos_tratamientos', function (Blueprint $table) {
            $table->dropColumn(['sesiones_recomendadas', 'intervalo_dias', 'protocolo', 'imagen_url', 'color']);
        });
    }
};
