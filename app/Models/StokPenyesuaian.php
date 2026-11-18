<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokPenyesuaian extends Model
{
    use HasFactory;

    protected $table = 'stok_penyesuaian';

    protected $fillable = [
        'bahan_id',
        'selisih',
        'alasan',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'selisih' => 'decimal:2',
        ];
    }

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}