<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinical_measurements', function (Blueprint $table) {
            // BMI puede superar 99.99 si la talla está mal tipeada. Ensanchamos a (5,2) → 999.99.
            $table->decimal('bmi', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clinical_measurements', function (Blueprint $table) {
            $table->decimal('bmi', 4, 2)->nullable()->change();
        });
    }
};
