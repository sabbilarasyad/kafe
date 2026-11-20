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
    // Tambahan harga untuk ukuran Large (opsional O1).
    public const TAMBAHAN_LARGE = 5000;

    // Pengali takaran untuk ukuran Large (bahan g dan ml saja).
    public const PENGALI_LARGE = 1.5;

    // Membuat pesanan. Aturannya sama untuk Kasir (web) dan Pelanggan (APK).
    // $items: [['menu_id' => 1, 'qty' => 2, 'ukuran' => 'Reguler'], ...]. Ukuran boleh kosong (dianggap Reguler).
    // Tugas: Membuat pesanan, menghitung tagihan, dan memotong bahan.
    // Kenapa: Semua langkah dilakukan bersama agar pesanan tidak tersimpan setengah jalan.
    // Contoh: 2 kopi membutuhkan 40 g kopi, lalu stok dikurangi 40 g.
    public function buat(User $pembuat, array $items, string $metodeBayar, ?int $nomorMeja = null): Pesanan
    {
        // Pastikan setiap item punya ukuran yang valid.
        $items = array_map(function ($item) {
            $item['ukuran'] = $item['ukuran'] ?? 'Reguler';
            return $item;
        }, $items);

        // Ambil semua menu yang dipesan, kuncinya id menu.
        $menus = Menu::with('resepItems.bahan')
            ->whereIn('id', collect($items)->pluck('menu_id'))
            ->get()
            ->keyBy('id');

        // Menu nonaktif atau tanpa resep ditolak.
        $this->tolakBilaTidakBisaDipesan($items, $menus);

        // Large hanya untuk Minuman.
        $this->tolakUkuranTidakValid($items, $menus);

        // Total kebutuhan tiap bahan dari SEMUA item (sudah memperhitungkan ukuran).
        $kebutuhan = $this->gabungKebutuhan($items, $menus);

        // Cek stok, simpan pesanan, dan potong stok dalam SATU transaksi.
        return DB::transaction(function () use ($pembuat, $items, $metodeBayar, $nomorMeja, $menus, $kebutuhan) {

            // Stok kurang: tolak (stok belum disentuh).
            $kekurangan = $this->cariKekurangan($kebutuhan);

            if (count($kekurangan) > 0) {
                throw ValidationException::withMessages(['stok' => $kekurangan]);
            }

            // Subtotal dari harga menu saat ini (+ tambahan Large), dihitung di server.
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $this->hargaSatuan($item, $menus) * $item['qty'];
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
                    'ukuran' => $item['ukuran'],
                    'harga_satuan' => $this->hargaSatuan($item, $menus),
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

    // Harga satu porsi sesuai ukuran. Reguler = harga menu, Large = harga menu + tambahan.
    // Tugas: Menentukan harga satu item sesuai ukuran yang dipesan.
    // Kenapa: Harga dihitung dari data menu di server, bukan dari kiriman aplikasi.
    // Contoh: Harga Rp20.000 ditambah Rp5.000 untuk ukuran Large.
    private function hargaSatuan(array $item, $menus): int
    {
        $harga = (int) $menus[$item['menu_id']]->harga;

        return $item['ukuran'] === 'Large' ? $harga + self::TAMBAHAN_LARGE : $harga;
    }

    // Service 5% dari subtotal, pajak 10% dari (subtotal + service).
    // Tugas: Menghitung biaya layanan dan pajak dari subtotal.
    // Kenapa: Pajak dihitung setelah biaya layanan agar dasar hitungnya konsisten.
    // Contoh: Subtotal Rp100.000 menghasilkan layanan Rp5.000 dan pajak Rp10.500.
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

    // Ukuran hanya Reguler atau Large, dan Large hanya untuk kategori Minuman.
    private function tolakUkuranTidakValid(array $items, $menus): void
    {
        foreach ($items as $item) {
            $menu = $menus[$item['menu_id']];

            if (! in_array($item['ukuran'], ['Reguler', 'Large'], true)) {
                throw ValidationException::withMessages([
                    'items' => 'Ukuran ' . $item['ukuran'] . ' tidak dikenal.',
                ]);
            }

            if ($item['ukuran'] === 'Large' && $menu->kategori !== 'Minuman') {
                throw ValidationException::withMessages([
                    'items' => $menu->nama . ' tidak tersedia dalam ukuran Large.',
                ]);
            }
        }
    }

    // Total kebutuhan tiap bahan dari seluruh item (kunci: id bahan).
    // Untuk Large, takaran g dan ml dikali 1,5; takaran pcs tidak berubah.
    // Tugas: Menjumlahkan kebutuhan bahan dari semua item pesanan.
    // Kenapa: Stok diperiksa untuk jumlah gabungan agar bahan tidak terhitung terpisah.
    // Contoh: Dua menu yang masing-masing perlu 20 g memakai total 40 g.
    private function gabungKebutuhan(array $items, $menus): array
    {
        $kebutuhan = [];

        foreach ($items as $item) {
            $menu = $menus[$item['menu_id']];

            foreach ($menu->resepItems as $resep) {
                $takaran = (float) $resep->takaran;

                if ($item['ukuran'] === 'Large' && in_array($resep->bahan->satuan, ['g', 'ml'], true)) {
                    $takaran *= self::PENGALI_LARGE;
                }

                $jumlah = $takaran * (int) $item['qty'];
                $kebutuhan[$resep->bahan_id] = ($kebutuhan[$resep->bahan_id] ?? 0) + $jumlah;
            }
        }

        return $kebutuhan;
    }

    // Daftar pesan untuk bahan yang stoknya tidak cukup (kosong = semua cukup).
    // Tugas: Menemukan bahan yang jumlah stoknya kurang dari kebutuhan.
    // Kenapa: Pesanan ditolak sebelum stok berubah bila satu bahan saja tidak cukup.
    // Contoh: Perlu 30 g dengan stok 20 g menghasilkan pesan kekurangan.
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