<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinical_measurements', function (Blueprint $table) {
            $table->decimal('arm_right_cm', 5, 2)->nullable()->after('chest_cm');
            $table->decimal('arm_left_cm', 5, 2)->nullable()->after('arm_right_cm');
            $table->decimal('thigh_right_cm', 5, 2)->nullable()->after('arm_left_cm');
            $table->decimal('thigh_left_cm', 5, 2)->nullable()->after('thigh_right_cm');
        });
    }

    public function down(): void
    {
        Schema::table('clinical_measurements', function (Blueprint $table) {
            $table->dropColumn(['arm_right_cm', 'arm_left_cm', 'thigh_right_cm', 'thigh_left_cm']);
        });
    }
};
