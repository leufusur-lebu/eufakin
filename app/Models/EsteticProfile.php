<?php

namespace App\Models;

use App\Models\Estetic\Appointment;
use App\Models\Estetic\Payment as EsteticPayment;
use App\Models\Estetic\SessionPhoto;
use App\Models\Estetic\Treatment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EsteticProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id', 'skin_type', 'observations', 'active',
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
        return $this->hasMany(Treatment::class, 'estetic_profile_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'estetic_profile_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(EsteticPayment::class, 'estetic_profile_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SessionPhoto::class, 'estetic_profile_id')->latest('tomada_at');
    }
}
