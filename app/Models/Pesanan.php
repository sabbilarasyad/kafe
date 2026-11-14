<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $fillable = [
        'user_id', 'nomor_meja', 'metode_bayar', 'status',
        'subtotal', 'service', 'pajak', 'total',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PesananItem::class);
    }

    public function bahanTerpotong()
    {
        return $this->hasMany(PesananBahan::class);
    }
}
