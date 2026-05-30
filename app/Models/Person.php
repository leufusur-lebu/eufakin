<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rut', 'first_name', 'last_name', 'nickname',
        'birth_date', 'gender', 'phone', 'email',
        'address', 'poblacion', 'comuna',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        'profile_photo_path',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function clinicalProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ClinicalProfile::class);
    }

    public function measurements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClinicalMeasurement::class)->orderByDesc('measured_at');
    }

    public function clinicalEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClinicalEvent::class)->orderByDesc('event_date');
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClinicalAttachment::class)->latest();
    }

    public function gymProfile(): HasOne
    {
        return $this->hasOne(GymProfile::class);
    }

    public function kineProfile(): HasOne
    {
        return $this->hasOne(KineProfile::class);
    }

    public function esteticProfile(): HasOne
    {
        return $this->hasOne(EsteticProfile::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;
        $like = '%' . $term . '%';
        return $query->where(function ($q) use ($like) {
            $q->where('first_name', 'like', $like)
              ->orWhere('last_name', 'like', $like)
              ->orWhere('rut', 'like', $like)
              ->orWhere('email', 'like', $like)
              ->orWhere('phone', 'like', $like);
        });
    }
}
