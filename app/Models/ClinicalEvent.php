<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicalEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'person_id', 'type', 'event_date', 'description',
        'severity', 'status', 'body_region', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'event_date' => 'date',
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

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['activo', 'en_tratamiento']);
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'lesion'           => 'exclamation-triangle',
            'cirugia'          => 'scissors',
            'hospitalizacion'  => 'building-office',
            'alergia_grave'    => 'shield-exclamation',
            'vacuna'           => 'beaker',
            'enfermedad'       => 'heart',
            default            => 'tag',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'lesion'           => 'Lesión',
            'cirugia'          => 'Cirugía',
            'hospitalizacion'  => 'Hospitalización',
            'alergia_grave'    => 'Alergia grave',
            'vacuna'           => 'Vacuna',
            'enfermedad'       => 'Enfermedad',
            default            => 'Otro',
        };
    }
}
