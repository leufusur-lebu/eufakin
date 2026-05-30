<?php

namespace App\Models;

use App\Models\Kine\Appointment;
use App\Models\Kine\Payment as KinePayment;
use App\Models\Kine\SessionPhoto;
use App\Models\Kine\Treatment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KineProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id', 'health_insurance', 'insurance_number', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'kine_profile_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'kine_profile_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(KinePayment::class, 'kine_profile_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SessionPhoto::class, 'kine_profile_id')->latest('tomada_at');
    }
}
