<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kine_tratamientos', function (Blueprint $table) {
            $table->foreignId('tipo_tratamiento_id')->nullable()->after('professional_id')
                ->constrained('kine_tipos_tratamientos')->nullOnDelete();
            $table->string('zona_tratada', 255)->nullable()->after('diagnostico');
        });
    }

    public function down(): void
    {
        Schema::table('kine_tratamientos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_tratamiento_id');
            $table->dropColumn('zona_tratada');
        });
    }
};
