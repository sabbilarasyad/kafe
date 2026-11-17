<?php

namespace App\Services;

use App\Models\Bahan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananBahan;
use App\Models\PesananItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesananService
{
    // Membuat pesanan. Aturannya sama untuk Kasir (web) dan Pelanggan (APK).
    public function buat(User $pembuat, array $items, string $metodeBayar, ?int $nomorMeja = null): Pesanan
    {
        // Ambil semua menu yang dipesan, kuncinya id menu.
        $menus = Menu::with('resepItems.bahan')
            ->whereIn('id', collect($items)->pluck('menu_id'))
            ->get()
            ->keyBy('id');

        // Menu nonaktif atau tanpa resep ditolak.
        $this->tolakBilaTidakBisaDipesan($items, $menus);

        // Total kebutuhan tiap bahan dari SEMUA item.
        $kebutuhan = $this->gabungKebutuhan($items, $menus);

        // Cek stok, simpan pesanan, dan potong stok dalam SATU transaksi.
        return DB::transaction(function () use ($pembuat, $items, $metodeBayar, $nomorMeja, $menus, $kebutuhan) {

            // Stok kurang: tolak (stok belum disentuh).
            $kekurangan = $this->cariKekurangan($kebutuhan);

            if (count($kekurangan) > 0) {
                throw ValidationException::withMessages(['stok' => $kekurangan]);
            }

            // Subtotal dari harga menu saat ini, dihitung di server.
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $menus[$item['menu_id']]->harga * $item['qty'];
            }

            $biaya = $this->hitungBiaya($subtotal);

            $pesanan = Pesanan::create([
                'user_id' => $pembuat->id,
                'nomor_meja' => $nomorMeja,
                'metode_bayar' => $metodeBayar,
                'status' => 'Baru',
                'subtotal' => $subtotal,
                'service' => $biaya['service'],
                'pajak' => $biaya['pajak'],
                'total' => $subtotal + $biaya['service'] + $biaya['pajak'],
            ]);

            // Rincian item dengan harga saat ini (struk lama tidak berubah kalau harga menu diubah).
            foreach ($items as $item) {
                PesananItem::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $item['menu_id'],
                    'qty' => $item['qty'],
                    'harga_satuan' => $menus[$item['menu_id']]->harga,
                ]);
            }

            // Potong stok dan catat bahan yang terpotong (untuk pengembalian saat batal).
            foreach ($kebutuhan as $bahanId => $jumlah) {
                Bahan::findOrFail($bahanId)->decrement('stok', $jumlah);

                PesananBahan::create([
                    'pesanan_id' => $pesanan->id,
                    'bahan_id' => $bahanId,
                    'jumlah' => $jumlah,
                ]);
            }

            return $pesanan;
        });
    }

    // Service 5% dari subtotal, pajak 10% dari (subtotal + service).
    private function hitungBiaya(int $subtotal): array
    {
        $service = round($subtotal * 0.05);
        $pajak = round(($subtotal + $service) * 0.10);

        return ['service' => (int) $service, 'pajak' => (int) $pajak];
    }

    // Tolak bila ada menu yang nonaktif atau belum punya resep.
    private function tolakBilaTidakBisaDipesan(array $items, $menus): void
    {
        foreach ($items as $item) {
            $menu = $menus[$item['menu_id']];

            if (! $menu->aktif || $menu->resepItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => $menu->nama . ' tidak bisa dipesan.',
                ]);
            }
        }
    }

    // Total kebutuhan tiap bahan dari seluruh item (kunci: id bahan).
    private function gabungKebutuhan(array $items, $menus): array
    {
        $kebutuhan = [];

        foreach ($items as $item) {
            $menu = $menus[$item['menu_id']];

            foreach ($menu->resepItems as $resep) {
                $jumlah = (float) $resep->takaran * (int) $item['qty'];
                $kebutuhan[$resep->bahan_id] = ($kebutuhan[$resep->bahan_id] ?? 0) + $jumlah;
            }
        }

        return $kebutuhan;
    }

    // Daftar pesan untuk bahan yang stoknya tidak cukup (kosong = semua cukup).
    private function cariKekurangan(array $kebutuhan): array
    {
        $pesan = [];

        $bahanList = Bahan::whereIn('id', array_keys($kebutuhan))->get()->keyBy('id');

        foreach ($kebutuhan as $bahanId => $butuh) {
            $bahan = $bahanList[$bahanId];
            $stok = (float) $bahan->stok;

            if ($butuh > $stok) {
                $pesan[] = 'Stok tidak cukup: ' . $bahan->nama
                    . ', butuh ' . $this->angka($butuh) . ' ' . $bahan->satuan
                    . ', sisa ' . $this->angka($stok) . ' ' . $bahan->satuan;
            }
        }

        return $pesan;
    }

    private function angka(float $nilai): string
    {
        return rtrim(rtrim(number_format($nilai, 2, ',', '.'), '0'), ',');
    }
}