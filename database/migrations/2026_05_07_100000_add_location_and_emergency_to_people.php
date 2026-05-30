<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('poblacion', 150)->nullable()->after('address');
            $table->string('comuna', 100)->nullable()->after('poblacion');
            $table->string('emergency_contact_name', 150)->nullable()->after('comuna');
            $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship', 50)->nullable()->after('emergency_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn([
                'poblacion', 'comuna',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
            ]);
        });
    }
};
