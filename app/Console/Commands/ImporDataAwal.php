<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Bahan;
use App\Models\Menu;

#[Signature('data:impor-awal')]
#[Description('Command description')]
class ImporDataAwal extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Membaca dan menghitung baris CSV.
        $path = database_path('data/awal/bahan.csv');
        $handle = fopen($path, 'r');

        fgetcsv($handle);

        $berhasil = 0;
        $dilewati = [];
        $baris = 1;

        while (($kolom = fgetcsv($handle)) !== false) {

        $baris++;

        $nama = trim($kolom[1]);
        $stok = trim($kolom[2]);
        $satuan = trim($kolom[3]);
        $harga = trim($kolom[4]);

        if ($satuan === 'kg') {
            $stok = $kolom[2] * 1000;
            $harga = $kolom[4] / 1000;
            $satuan = 'g';
        } elseif ($satuan === 'liter') {
            $stok = $kolom[2] * 1000;
            $harga = $kolom[4] / 1000;
            $satuan = 'ml';
        }

        $this->line("$nama | $stok | $harga | $satuan");

        $nama_kunci = strtolower(trim($nama));
        Bahan::updateOrCreate(
            [ 'nama_kunci' => $nama_kunci ],
            [   
                'nama' => $nama,
                'satuan' => $satuan,
                'stok' => $stok,
                'harga_persatuan' => $harga,
            ]
        );
        $berhasil++;
        }

        fclose($handle);

        $this->info("Berhasil: {$berhasil} bahan");
        foreach ($dilewati as $item) {
            $this->warn($item);
        }

        $this->importMenu();
    }

    public function importMenu() 
    {
        // Membaca dan menghitung baris CSV.
        $path = database_path('data/awal/menu.csv');
        $handle = fopen($path, 'r');

        fgetcsv($handle);

        $berhasil = 0;
        $dilewati = [];
        $baris = 1;

        while (($kolom = fgetcsv($handle)) !== false) {

        $baris++;

        $kode = trim($kolom[0]);
        $nama = trim($kolom[1]);
        $kategori = trim($kolom[2]);
        $harga = (int)$kolom[3];

        Menu::updateOrCreate(
            [ 'kode' => $kode ],
            [   
                'nama' => $nama,
                'kategori' => $kategori,
                'harga' => $harga,
            ]
        );
        $berhasil++;
        }

        fclose($handle);

        $this->info("Berhasil: {$berhasil} Menu");
        foreach ($dilewati as $item) {
            $this->warn($item);
        }
    }
}