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

    // Berapa porsi menu ini yang masih bisa dibuat dari stok sekarang.
    public function porsiTersedia(): int
    {
        if ($this->resepItems->isEmpty()) {
            return 0;
        }

        return (int) $this->resepItems
            ->map(fn ($item) => floor($item->bahan->stok / (float)$item->takaran))
            ->min();
    }
}