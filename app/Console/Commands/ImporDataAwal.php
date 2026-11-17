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
        $jumlah = 0;
        while (fgetcsv($handle) !== false){
            $jumlah++;
        }
        fclose($handle);
        $this->info('Baris data bahan: ' . ($jumlah - 1));
    }
}
