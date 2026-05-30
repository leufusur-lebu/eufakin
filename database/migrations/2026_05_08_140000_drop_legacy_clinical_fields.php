<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kine_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('kine_profiles', 'background')) {
                $table->dropColumn('background');
            }
            if (Schema::hasColumn('kine_profiles', 'observations')) {
                $table->dropColumn('observations');
            }
        });

        Schema::table('estetic_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('estetic_profiles', 'allergies')) {
                $table->dropColumn('allergies');
            }
            if (Schema::hasColumn('estetic_profiles', 'medical_observations')) {
                $table->dropColumn('medical_observations');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kine_profiles', function (Blueprint $table) {
            $table->text('background')->nullable();
            $table->text('observations')->nullable();
        });

        Schema::table('estetic_profiles', function (Blueprint $table) {
            $table->text('allergies')->nullable();
            $table->text('medical_observations')->nullable();
        });
    }
};
