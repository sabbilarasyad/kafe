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

    // HPP menu = jumlah (takaran x harga bahan per satuan dasar).
    // Hasilnya angka, atau null kalau tidak bisa dihitung
    public function hpp(): ?float
    {
        if ($this->resepItems->isEmpty()) {
            return null;
        }

        $total = 0;

        foreach ($this->resepItems as $item){
            if ($item->bahan->harga_per_satuan === null) {
                return null;
            }

            $total += (float) $item->takaran * (float) $item->bahan->harga_per_satuan;
        }

        return $total;
    }
}