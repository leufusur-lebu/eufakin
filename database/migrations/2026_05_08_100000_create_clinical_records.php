<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ==== 1. Ficha clínica (datos baseline) ====
        Schema::create('clinical_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->unique()->constrained('people')->cascadeOnDelete();

            // Sangre y donante
            $table->enum('blood_type', ['A+','A-','B+','B-','AB+','AB-','O+','O-','desconocido'])->nullable();
            $table->boolean('donor')->default(false);

            // Antecedentes
            $table->text('chronic_diseases')->nullable();      // Diabetes, hipertensión, asma...
            $table->text('chronic_medications')->nullable();   // Medicamentos habituales
            $table->text('allergies')->nullable();             // Medicamentos, alimentos, contacto
            $table->text('family_history')->nullable();        // Antecedentes familiares
            $table->text('surgical_history')->nullable();      // Cirugías previas

            // Embarazo
            $table->boolean('is_pregnant')->default(false);
            $table->unsignedTinyInteger('pregnancy_weeks')->nullable();

            // Hábitos
            $table->enum('smoker', ['no','ex','ocasional','habitual'])->nullable();
            $table->enum('alcohol', ['no','ocasional','frecuente'])->nullable();
            $table->enum('exercise_frequency', ['sedentario','ocasional','regular','intenso'])->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ==== 2. Mediciones (compatible INBODY) ====
        Schema::create('clinical_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->dateTime('measured_at');

            // Básicas
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->decimal('bmi', 4, 2)->nullable();           // calculado

            // Composición corporal (INBODY)
            $table->decimal('body_fat_kg', 5, 2)->nullable();
            $table->decimal('body_fat_percent', 4, 2)->nullable();
            $table->decimal('skeletal_muscle_kg', 5, 2)->nullable();
            $table->decimal('soft_lean_mass_kg', 5, 2)->nullable();
            $table->decimal('fat_free_mass_kg', 5, 2)->nullable();
            $table->decimal('protein_kg', 5, 2)->nullable();
            $table->decimal('mineral_kg', 5, 2)->nullable();

            // Agua corporal
            $table->decimal('total_body_water_l', 5, 2)->nullable();
            $table->decimal('intracellular_water_l', 5, 2)->nullable();
            $table->decimal('extracellular_water_l', 5, 2)->nullable();
            $table->decimal('ecw_tbw_ratio', 4, 3)->nullable();

            // Visceral / metabolismo
            $table->unsignedSmallInteger('visceral_fat_area')->nullable();   // cm²
            $table->unsignedSmallInteger('visceral_fat_level')->nullable();
            $table->unsignedSmallInteger('bmr_kcal')->nullable();
            $table->decimal('phase_angle', 4, 2)->nullable();
            $table->unsignedTinyInteger('inbody_score')->nullable();

            // Antropométricas
            $table->decimal('waist_cm', 5, 2)->nullable();
            $table->decimal('hip_cm', 5, 2)->nullable();
            $table->decimal('chest_cm', 5, 2)->nullable();
            $table->decimal('whr', 4, 3)->nullable();           // waist-hip ratio

            // Segmental (JSON: {"right_arm_kg": ..., "left_arm_kg": ..., "trunk_kg": ..., "right_leg_kg": ..., "left_leg_kg": ...})
            $table->json('segmental_lean')->nullable();
            $table->json('segmental_fat')->nullable();

            // Signos vitales
            $table->unsignedSmallInteger('blood_pressure_systolic')->nullable();
            $table->unsignedSmallInteger('blood_pressure_diastolic')->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('glucose_mg_dl')->nullable();

            // Origen de la medición (para saber si vino de INBODY, manual, sesión kine, etc)
            $table->enum('source', ['manual','inbody','session_auto','admission'])->default('manual');

            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['person_id', 'measured_at']);
        });

        // ==== 3. Eventos clínicos (lesiones, cirugías, hospitalizaciones, etc) ====
        Schema::create('clinical_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->enum('type', ['lesion', 'cirugia', 'hospitalizacion', 'alergia_grave', 'vacuna', 'enfermedad', 'otro']);
            $table->date('event_date');
            $table->string('description', 255);
            $table->enum('severity', ['leve','moderada','grave'])->nullable();
            $table->enum('status', ['activo','en_tratamiento','resuelto'])->default('activo');
            $table->string('body_region', 150)->nullable();    // ej. "tobillo derecho"
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['person_id', 'status']);
            $table->index(['person_id', 'event_date']);
        });

        // ==== 4. Adjuntos clínicos (exámenes, RX, informes) ====
        Schema::create('clinical_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->foreignId('clinical_event_id')->nullable()->constrained('clinical_events')->nullOnDelete();
            $table->foreignId('clinical_measurement_id')->nullable()->constrained('clinical_measurements')->nullOnDelete();
            $table->enum('category', ['examen', 'imagen', 'informe', 'receta', 'inbody', 'otro'])->default('examen');
            $table->string('title', 200);
            $table->string('path', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('size_kb')->nullable();
            $table->date('document_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['person_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_attachments');
        Schema::dropIfExists('clinical_events');
        Schema::dropIfExists('clinical_measurements');
        Schema::dropIfExists('clinical_profiles');
    }
};
