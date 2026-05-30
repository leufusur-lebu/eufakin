<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estetic_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('skin_type', 100)->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_observations')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique('person_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estetic_profiles');
    }
};
