<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananBahan;
use App\Models\PesananItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    // Halaman Kasir membuat pesanan: hanya menu yang aktif.
    public function create()
    {
        $menu = Menu::with('resepItems.bahan')
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        return view('pesanan.create', compact('menu'));
    }

    // Kasir menekan "Konfirmasi pesanan".
    public function store(Request $request)
    {
        // 1. Periksa bentuk input.
        $data = $request->validate([
            'metode_bayar' => ['required', Rule::in(['Tunai', 'QRIS'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:menu,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        // 2. Ambil semua menu yang dipesan, kuncinya id menu.
        $menus = Menu::with('resepItems.bahan')
            ->whereIn('id', collect($data['items'])->pluck('menu_id'))
            ->get()
            ->keyBy('id');

        // 3. Menu nonaktif atau tanpa resep ditolak.
        $this->tolakBilaTidakBisaDipesan($data['items'], $menus);

        // 4. Total kebutuhan tiap bahan dari SEMUA item.
        $kebutuhan = $this->gabungKebutuhan($data['items'], $menus);

        // 5. Cek stok, simpan pesanan, dan potong stok dalam SATU transaksi.
        //    Kalau ada yang gagal, semuanya dibatalkan.
        $pesanan = DB::transaction(function () use ($data, $menus, $kebutuhan, $request) {

            // 5a. Stok kurang: tolak (stok belum disentuh).
            $kekurangan = $this->cariKekurangan($kebutuhan);

            if (count($kekurangan) > 0) {
                throw ValidationException::withMessages(['stok' => $kekurangan]);
            }

            // 5b. Subtotal dari harga menu saat ini, dihitung di server.
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $menus[$item['menu_id']]->harga * $item['qty'];
            }

            $biaya = $this->hitungBiaya($subtotal);

            // 5c. Simpan kepala pesanan.
            $pesanan = Pesanan::create([
                'user_id' => $request->user()->id,
                'nomor_meja' => null,
                'metode_bayar' => $data['metode_bayar'],
                'status' => 'Baru',
                'subtotal' => $subtotal,
                'service' => $biaya['service'],
                'pajak' => $biaya['pajak'],
                'total' => $subtotal + $biaya['service'] + $biaya['pajak'],
            ]);

            // 5d. Simpan rincian item dengan harga saat ini
            //     (supaya struk lama tidak berubah kalau harga menu diubah).
            foreach ($data['items'] as $item) {
                PesananItem::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $item['menu_id'],
                    'qty' => $item['qty'],
                    'harga_satuan' => $menus[$item['menu_id']]->harga,
                ]);
            }

            // 5e. Potong stok dan catat bahan yang terpotong
            //     (untuk dikembalikan saat pesanan dibatalkan).
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

        return redirect('/pesanan/' . $pesanan->id . '/struk')->with('baru', true);
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
                // Kebutuhan bahan ini untuk item ini = takaran x jumlah porsi.
                $jumlah = (float) $resep->takaran * (int) $item['qty'];

                // Tambahkan ke catatan bahan ini. Kalau belum ada, mulai dari 0.
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

    public function struk(Pesanan $pesanan){
        $pesanan->load('items.menu');

        return view('pesanan.struk', compact('pesanan'));
    }

    // Merapikan angka: 450.00 jadi 450.
    private function angka(float $nilai): string
    {
        return rtrim(rtrim(number_format($nilai, 2, ',', '.'), '0'), ',');
    }
}