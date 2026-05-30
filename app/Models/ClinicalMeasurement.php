<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicalMeasurement extends Model
{
    protected $fillable = [
        'person_id', 'measured_at',
        'weight_kg', 'height_cm', 'bmi',
        'body_fat_kg', 'body_fat_percent',
        'skeletal_muscle_kg', 'soft_lean_mass_kg', 'fat_free_mass_kg',
        'protein_kg', 'mineral_kg',
        'total_body_water_l', 'intracellular_water_l', 'extracellular_water_l', 'ecw_tbw_ratio',
        'visceral_fat_area', 'visceral_fat_level', 'bmr_kcal', 'phase_angle', 'inbody_score',
        'waist_cm', 'hip_cm', 'chest_cm', 'whr',
        'arm_right_cm', 'arm_left_cm', 'thigh_right_cm', 'thigh_left_cm',
        'segmental_lean', 'segmental_fat',
        'blood_pressure_systolic', 'blood_pressure_diastolic',
        'heart_rate', 'glucose_mg_dl',
        'source', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'measured_at'  => 'datetime',
        'segmental_lean' => 'array',
        'segmental_fat'  => 'array',
        'weight_kg' => 'decimal:2',
        'bmi' => 'decimal:2',
        'body_fat_kg' => 'decimal:2',
        'body_fat_percent' => 'decimal:2',
        'skeletal_muscle_kg' => 'decimal:2',
        'phase_angle' => 'decimal:2',
        'whr' => 'decimal:3',
        'ecw_tbw_ratio' => 'decimal:3',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClinicalAttachment::class);
    }

    /**
     * Calcula IMC desde peso y talla si están presentes y son realistas.
     * Devuelve null si los valores son fuera de rango humano razonable.
     */
    public static function computeBmi(?float $weight, ?int $height): ?float
    {
        if (!$weight || !$height) return null;
        // Sanidad: talla humana 30–250 cm, peso 1–500 kg
        if ($height < 30 || $height > 250) return null;
        if ($weight < 1  || $weight > 500) return null;

        $h = $height / 100;
        $bmi = $weight / ($h * $h);
        // Capamos a 999.99 por si pasa una validación con datos extremos
        return round(min($bmi, 999.99), 2);
    }

    /**
     * Calcula WHR desde cintura y cadera.
     */
    public static function computeWhr(?float $waist, ?float $hip): ?float
    {
        if (!$waist || !$hip || $hip == 0) return null;
        return round(min($waist / $hip, 9.999), 3);
    }

    public function getBmiCategoryAttribute(): ?string
    {
        $b = $this->bmi;
        if (!$b) return null;
        return match(true) {
            $b < 18.5 => 'Bajo peso',
            $b < 25   => 'Normal',
            $b < 30   => 'Sobrepeso',
            $b < 35   => 'Obesidad I',
            $b < 40   => 'Obesidad II',
            default   => 'Obesidad III',
        };
    }
}
