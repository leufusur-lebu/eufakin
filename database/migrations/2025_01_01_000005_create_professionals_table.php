<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 100);
            $table->string('last_name', 100);
            $table->string('rut', 20)->nullable();
            $table->enum('module', ['kine', 'estetic', 'both']);
            $table->string('specialty', 150)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['module', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
