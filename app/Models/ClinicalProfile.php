<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalProfile extends Model
{
    protected $fillable = [
        'person_id', 'blood_type', 'donor',
        'chronic_diseases', 'chronic_medications', 'allergies',
        'family_history', 'surgical_history',
        'is_pregnant', 'pregnancy_weeks',
        'smoker', 'alcohol', 'exercise_frequency',
        'notes', 'updated_by',
    ];

    protected $casts = [
        'donor' => 'boolean',
        'is_pregnant' => 'boolean',
        'pregnancy_weeks' => 'integer',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
