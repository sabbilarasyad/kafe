<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananBahan extends Model
{
    protected $table = 'pesanan_bahan';

    protected $fillable = ['pesanan_id', 'bahan_id', 'jumlah'];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function bahan()
    {
        return $this->belongsTo(Bahan::class);
    }
}