<?php

namespace App\Services;

use App\Models\Menu;

class KetersediaanMenu
{
    // Sisa porsi yang masih bisa dibuat dari stok sekarang. Null = belum ada resep.
    // Tugas: Menghitung jumlah porsi maksimum berdasarkan bahan yang paling membatasi.
    // Kenapa: Satu resep hanya bisa dibuat sebanyak bahan dengan stok relatif paling sedikit.
    // Contoh: Stok cukup untuk 8 porsi kopi dan 5 porsi susu berarti tersedia 5 porsi.
    public static function sisaPorsi(Menu $menu): ?int
    {
        $menu->loadMissing('resepItems.bahan');

        if ($menu->resepItems->isEmpty()) {
            return null;
        }

        $porsi = null;

        foreach ($menu->resepItems as $item) {
            $takaran = (float) $item->takaran;

            if ($takaran <= 0) {
                continue;
            }

            $bisa = (int) floor((float) $item->bahan->stok / $takaran);
            $porsi = $porsi === null ? $bisa : min($porsi, $bisa);
        }

        return $porsi ?? 0;
    }

    // Habis = tidak cukup untuk 1 porsi. Menu tanpa resep juga dianggap habis.
    // Tugas: Menentukan apakah menu tidak dapat dibuat satu porsi pun.
    // Kenapa: Menu tanpa resep tidak bisa dihitung sebagai menu yang tersedia.
    // Contoh: Sisa 0 porsi berarti habis, sedangkan sisa 1 porsi berarti belum habis.
    public static function habis(Menu $menu): bool
    {
        $sisa = self::sisaPorsi($menu);

        return $sisa === null || $sisa < 1;
    }
}