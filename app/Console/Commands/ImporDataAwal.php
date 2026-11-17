<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

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
        $berhasil++;
        }

        fclose($handle);

        $this->info("Berhasil: {$berhasil} bahan");
        foreach ($dilewati as $item) {
            $this->warn($item);
        }
    }
}