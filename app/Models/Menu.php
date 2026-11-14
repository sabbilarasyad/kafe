<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    protected $fillable = [
        'nama',
        'kategori',
        'harga',
        'aktif',
    ];

    public function resepItems()
    {
        return $this->hasMany(ResepItem::class);
    }
}