<?php

namespace Database\Seeders;

use App\Models\Bahan;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    // Data demo: hanya mengatur keadaan awal stok, tidak membuat pesanan.
    // Susu UHT (BHN-003) = 0,3 liter = 300 ml. Aman dijalankan berulang.
    public function run(): void
    {
        Bahan::where('kode', 'BHN-003')->update(['stok' => 300]);
    }
}