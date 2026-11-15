<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMasuk extends Model
{
    protected $table = 'stok_masuk';

    protected $fillable = ['tanggal', 'bahan_id', 'jumlah', 'user_id'];
    protected $casts = ['tanggal' => 'date'];

    public function bahan()
    {
        return $this->belongsTo(Bahan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}