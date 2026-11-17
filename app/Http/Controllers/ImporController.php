<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class ImporController extends Controller
{
    // Berkas data awal yang dibaca dari database/data/awal.
    private array $berkas = [
        'bahan.csv' => 'Satuan campuran: kg, g, liter, ml, pcs',
        'menu.csv' => 'Nama menu, kategori, harga jual',
        'resep.csv' => 'Nama bahan bisa salah ketik',
    ];

    public function index()
    {
        $daftar = [];

        foreach ($this->berkas as $nama => $keterangan) {
            $daftar[] = [
                'nama' => $nama,
                'keterangan' => $keterangan,
                'ada' => file_exists(database_path('data/awal/' . $nama)),
            ];
        }

        return view('impor.index', [
            'berkas' => $daftar,
            'hasil' => session('hasil'),
            'pencocokan' => session('pencocokan'),
        ]);
    }

    public function jalankan()
    {
        // Menjalankan perintah impor yang sama dengan di terminal, lalu membaca laporannya.
        Artisan::call('data:impor-awal');
        $baris = preg_split('/\R/', trim(Artisan::output()));

        $hasil = [];
        $pencocokan = [];

        foreach ($baris as $b) {
            $b = trim($b);

            // Contoh: "bahan.csv: 20 berhasil, 0 dicocokkan, 0 dilewati"
            if (preg_match('/^(\S+\.csv): (\d+) berhasil, (\d+) dicocokkan, (\d+) dilewati/', $b, $m)) {
                $hasil[] = ['berkas' => $m[1], 'berhasil' => $m[2], 'dicocokkan' => $m[3], 'dilewati' => $m[4]];
            } elseif (str_starts_with($b, '~')) {
                // Contoh: "~ baris 18: 'Biji kopi arabica' dicocokkan ke 'Biji kopi arabika'"
                if (preg_match("/'(.+?)' dicocokkan ke '(.+?)'/", $b, $m)) {
                    $pencocokan[] = ['nama' => $m[1], 'ke' => $m[2], 'ket' => 'Salah ketik, dicocokkan', 'dilewati' => false];
                }
            } elseif (str_starts_with($b, '!')) {
                // Contoh: "! resep.csv baris 66: bahan 'Sirup vanila' tidak terdaftar di bahan.csv ..."
                if (preg_match("/bahan '(.+?)' tidak terdaftar/", $b, $m)) {
                    $pencocokan[] = ['nama' => $m[1], 'ke' => '—', 'ket' => 'Tidak ada di bahan.csv', 'dilewati' => true];
                } else {
                    $pencocokan[] = ['nama' => ltrim($b, '! '), 'ke' => '—', 'ket' => 'Dilewati', 'dilewati' => true];
                }
            }
        }

        return redirect('/impor')->with(['hasil' => $hasil, 'pencocokan' => $pencocokan]);
    }
}