<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananItem extends Model
{
    protected $table = 'pesanan_item';

    protected $fillable = ['pesanan_id', 'menu_id', 'qty', 'harga_satuan'];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
