<?php

namespace App\Models\Estetic;

use App\Models\EsteticProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SessionPhoto extends Model
{
    protected $table = 'este_session_photos';

    protected $fillable = [
        'estetic_profile_id', 'sesion_id', 'tratamiento_id',
        'tipo', 'path', 'caption', 'tomada_at',
    ];

    protected $casts = [
        'tomada_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(EsteticProfile::class, 'estetic_profile_id');
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(Sesion::class, 'sesion_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'tratamiento_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
