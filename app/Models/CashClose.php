<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashClose extends Model
{
    protected $fillable = [
        'fecha', 'total_sistema', 'total_efectivo_sistema', 'total_efectivo_contado',
        'diferencia', 'breakdown_metodos', 'breakdown_modulos',
        'total_transacciones', 'observaciones', 'closed_by', 'closed_at',
    ];

    protected $casts = [
        'fecha' => 'date',
        'closed_at' => 'datetime',
        'total_sistema' => 'decimal:2',
        'total_efectivo_sistema' => 'decimal:2',
        'total_efectivo_contado' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'breakdown_metodos' => 'array',
        'breakdown_modulos' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
