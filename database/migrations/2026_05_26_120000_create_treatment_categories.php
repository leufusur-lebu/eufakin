<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabla de categorías editables
        Schema::create('treatment_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('module', ['kine', 'estetic']);
            $table->string('key', 50);
            $table->string('label', 100);
            $table->string('icon', 50)->default('tag');
            $table->string('color', 20)->default('zinc');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['module', 'key']);
            $table->index(['module', 'sort_order']);
        });

        // Cambiar enum a varchar en este_tipos_tratamientos
        Schema::table('este_tipos_tratamientos', function (Blueprint $table) {
            $table->string('categoria', 50)->default('otro')->change();
        });

        // Cambiar enum a varchar en kine_tipos_tratamientos
        Schema::table('kine_tipos_tratamientos', function (Blueprint $table) {
            $table->string('categoria', 50)->default('otro')->change();
        });

        // Sembrar valores iniciales
        $now = now();

        $kineCategories = [
            ['traumatologia',  'Traumatología',  'bolt',         'sky',    1],
            ['neurologia',     'Neurología',     'cpu-chip',     'violet', 2],
            ['respiratoria',   'Respiratoria',   'cloud',        'teal',   3],
            ['deportiva',      'Deportiva',      'trophy',       'amber',  4],
            ['postquirurgica', 'Postquirúrgica', 'heart',        'rose',   5],
            ['otro',           'Otros',          'tag',          'zinc',   99],
        ];

        $estetCategories = [
            ['facial',     'Facial',     'sparkles',    'pink',    1],
            ['cuerpo',     'Corporal',   'sun',         'rose',    2],
            ['masajes',    'Masajes',    'hand-raised', 'amber',   3],
            ['depilacion', 'Depilación', 'scissors',    'violet',  4],
            ['reductivos', 'Reductivos', 'fire',        'fuchsia', 5],
            ['otro',       'Otros',      'tag',         'zinc',    99],
        ];

        $rows = [];
        foreach ($kineCategories as [$key, $label, $icon, $color, $order]) {
            $rows[] = [
                'module' => 'kine', 'key' => $key, 'label' => $label,
                'icon' => $icon, 'color' => $color, 'sort_order' => $order,
                'activo' => true, 'created_at' => $now, 'updated_at' => $now,
            ];
        }
        foreach ($estetCategories as [$key, $label, $icon, $color, $order]) {
            $rows[] = [
                'module' => 'estetic', 'key' => $key, 'label' => $label,
                'icon' => $icon, 'color' => $color, 'sort_order' => $order,
                'activo' => true, 'created_at' => $now, 'updated_at' => $now,
            ];
        }
        DB::table('treatment_categories')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_categories');

        // Restaurar enums originales (best-effort)
        Schema::table('este_tipos_tratamientos', function (Blueprint $table) {
            $table->enum('categoria', ['facial', 'cuerpo', 'masajes', 'depilacion', 'otro'])->default('otro')->change();
        });

        Schema::table('kine_tipos_tratamientos', function (Blueprint $table) {
            $table->enum('categoria', ['traumatologia', 'neurologia', 'respiratoria', 'deportiva', 'postquirurgica', 'otro'])->default('otro')->change();
        });
    }
};
