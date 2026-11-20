<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    protected $fillable = [
        'kode',
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
    // Tugas: Menghitung porsi yang dapat dibuat dari stok setiap bahan resep.
    // Kenapa: Jumlah porsi dibatasi oleh bahan yang paling cepat habis.
    // Contoh: Bahan A cukup 6 porsi dan bahan B cukup 4, hasilnya 4 porsi.
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
    // Tugas: Menghitung total biaya bahan untuk satu porsi menu.
    // Kenapa: Nilai null dipakai saat resep kosong atau harga salah satu bahan belum tersedia.
    // Contoh: 20 g dengan harga Rp100 per g menambah HPP Rp2.000.
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