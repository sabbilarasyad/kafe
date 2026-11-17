<?php

namespace App\Console\Commands;

use App\Models\Bahan;
use App\Models\Menu;
use App\Models\ResepItem;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('data:impor-awal')]
#[Description('Impor data awal klien (bahan, menu, resep) dari CSV')]
class ImporDataAwal extends Command
{
    public function handle(): int
    {
        // Urutan penting: resep butuh bahan dan menu yang sudah ada.
        $this->importBahan();
        $this->importMenu();
        $this->importResep();

        return self::SUCCESS;
    }

    // Membaca CSV baris demi baris (judul kolom dilewati). Kuncinya nomor baris di file.
    private function bacaCsv(string $nama): \Generator
    {
        $handle = fopen(database_path('data/awal/' . $nama), 'r');

        fgetcsv($handle);   // lewati judul kolom
        $baris = 1;

        while (($kolom = fgetcsv($handle)) !== false) {
            $baris++;
            yield $baris => $kolom;
        }

        fclose($handle);
    }

    // Ringkasan hasil impor satu berkas.
    private function laporkan(string $judul, int $berhasil, array $dicocokkan, array $dilewati): void
    {
        $this->info("$judul: $berhasil berhasil, " . count($dicocokkan) . ' dicocokkan, ' . count($dilewati) . ' dilewati');

        foreach ($dicocokkan as $d) {
            $this->line("  ~ $d");
        }
        foreach ($dilewati as $d) {
            $this->warn("  ! $d");
        }
    }

    private function importBahan(): void
    {
        $berhasil = 0;
        $dilewati = [];

        foreach ($this->bacaCsv('bahan.csv') as $baris => $kolom) {
            // Baris kosong, kolom kurang, atau angka tidak valid: lewati dan laporkan.
            if (count($kolom) < 5 || trim((string) $kolom[0]) === '' || trim((string) $kolom[1]) === ''
                || ! is_numeric($kolom[2]) || ! is_numeric($kolom[4])) {
                $dilewati[] = "bahan.csv baris $baris: kosong atau tidak valid";
                continue;
            }

            $kode = trim($kolom[0]);
            $nama = trim($kolom[1]);
            $stok = (float) $kolom[2];
            $satuan = strtolower(trim($kolom[3]));
            $harga = (float) $kolom[4];

            // Normalisasi satuan: kg jadi g, liter jadi ml.
            // Stok dikali 1000, harga per satuan dibagi 1000 (harga mengikuti satuan di barisnya).
            if ($satuan === 'kg') {
                $stok *= 1000;
                $harga /= 1000;
                $satuan = 'g';
            } elseif ($satuan === 'liter') {
                $stok *= 1000;
                $harga /= 1000;
                $satuan = 'ml';
            }

            if (! in_array($satuan, ['g', 'ml', 'pcs'], true)) {
                $dilewati[] = "bahan.csv baris $baris: satuan '{$kolom[3]}' tidak dikenal";
                continue;
            }

            // Cari lewat nama_kunci, supaya nama yang beda huruf besar/kecil atau spasi dianggap sama.
            $bahan = Bahan::firstOrNew(['nama_kunci' => strtolower($nama)]);
            $bahan->fill(['kode' => $kode, 'nama' => $nama, 'satuan' => $satuan]);

            // Stok dan harga hanya diisi saat bahan pertama kali dibuat (atau harga masih kosong),
            // supaya impor ulang tidak menimpa stok hasil penjualan atau harga dari Supplier.
            // batas_minimum otomatis 0 dari default database.
            if (! $bahan->exists) {
                $bahan->stok = $stok;
            }
            if (! $bahan->exists || $bahan->harga_per_satuan === null) {
                $bahan->harga_per_satuan = $harga;
            }

            $bahan->save();
            $berhasil++;
        }

        $this->laporkan('bahan.csv', $berhasil, [], $dilewati);
    }

    private function importMenu(): void
    {
        $berhasil = 0;
        $dilewati = [];

        foreach ($this->bacaCsv('menu.csv') as $baris => $kolom) {
            if (count($kolom) < 4 || trim((string) $kolom[0]) === '' || trim((string) $kolom[1]) === ''
                || ! is_numeric($kolom[3])) {
                $dilewati[] = "menu.csv baris $baris: kosong atau tidak valid";
                continue;
            }

            // firstOrCreate: menu yang sudah ada tidak ditimpa (harga yang diubah Manajer aman).
            Menu::firstOrCreate(
                ['kode' => trim($kolom[0])],
                [
                    'nama' => trim($kolom[1]),
                    'kategori' => trim($kolom[2]),
                    'harga' => (int) $kolom[3],
                ]
            );
            $berhasil++;
        }

        $this->laporkan('menu.csv', $berhasil, [], $dilewati);
    }

    private function importResep(): void
    {
        $berhasil = 0;
        $dicocokkan = [];
        $dilewati = [];

        foreach ($this->bacaCsv('resep.csv') as $baris => $kolom) {
            if (count($kolom) < 3 || trim((string) $kolom[0]) === '' || trim((string) $kolom[1]) === ''
                || ! is_numeric($kolom[2]) || (float) $kolom[2] <= 0) {
                $dilewati[] = "resep.csv baris $baris: kosong atau tidak valid";
                continue;
            }

            $kodeMenu = trim($kolom[0]);
            $namaBahan = trim($kolom[1]);
            $takaran = (float) $kolom[2];

            // Kalau satuan di resep kg atau liter, ubah ke satuan dasar (g, ml).
            $satuanResep = strtolower(trim($kolom[3] ?? ''));
            if ($satuanResep === 'kg' || $satuanResep === 'liter') {
                $takaran *= 1000;
            }

            // Cari menu dari kodenya.
            $menu = Menu::where('kode', $kodeMenu)->first();
            if (! $menu) {
                $dilewati[] = "resep.csv baris $baris: kode menu '$kodeMenu' tidak dikenal";
                continue;
            }

            // Cari bahan: persis, atau yang salah ketik.
            $bahan = $this->cariBahan($namaBahan);
            if (! $bahan) {
                $dilewati[] = "resep.csv baris $baris: bahan '$namaBahan' tidak terdaftar di bahan.csv (menu $kodeMenu)";
                continue;
            }

            // Kalau namanya tidak persis, catat bahwa ini hasil pencocokan.
            if ($bahan->nama_kunci !== strtolower($namaBahan)) {
                $dicocokkan[] = "baris $baris: '$namaBahan' dicocokkan ke '{$bahan->nama}'";
            }

            // updateOrCreate dengan pasangan menu + bahan: impor ulang tidak menggandakan.
            ResepItem::updateOrCreate(
                ['menu_id' => $menu->id, 'bahan_id' => $bahan->id],
                ['takaran' => $takaran]
            );
            $berhasil++;
        }

        $this->laporkan('resep.csv', $berhasil, $dicocokkan, $dilewati);
    }

    // Mencari bahan dari nama di resep.
    private function cariBahan(string $nama): ?Bahan
    {
        $kunci = strtolower(trim($nama));

        // 1. Nama persis (huruf besar/kecil dan spasi tepi diabaikan).
        $persis = Bahan::where('nama_kunci', $kunci)->first();
        if ($persis) {
            return $persis;
        }

       // 2. Salah ketik: cari bahan yang jaraknya TEPAT 1 dari nama itu.
        $kandidat = [];

        foreach (Bahan::all() as $b) {
            if (levenshtein($kunci, $b->nama_kunci) === 1) {
                $kandidat[] = $b;
            }
        }

        // Hanya yakin kalau kandidatnya satu.
        if (count($kandidat) === 1) {
            return $kandidat[0];
        }

        return null;
    }
}