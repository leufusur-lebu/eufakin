<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClinicalAttachment extends Model
{
    protected $fillable = [
        'person_id', 'clinical_event_id', 'clinical_measurement_id',
        'category', 'title', 'path', 'mime_type', 'size_kb',
        'document_date', 'notes', 'uploaded_by',
    ];

    protected $casts = [
        'document_date' => 'date',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(ClinicalEvent::class, 'clinical_event_id');
    }

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(ClinicalMeasurement::class, 'clinical_measurement_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
