<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResepItem extends Model
{
    protected $table = 'resep_item';

    protected $fillable = [
        'menu_id',
        'bahan_id',
        'takaran',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function bahan()
    {
        return $this->belongsTo(Bahan::class);
    }
}