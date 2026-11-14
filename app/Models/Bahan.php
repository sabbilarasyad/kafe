<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    protected $table = 'bahan';

    protected $fillable = [
        'nama',
        'nama_kunci',
        'satuan',
        'stok',
        'batas_minimum',
        'harga_per_satuan',
        'harga_diperbarui',
    ];

    public function resepItems()
    {
        return $this->hasMany(ResepItem::class);
    }
}