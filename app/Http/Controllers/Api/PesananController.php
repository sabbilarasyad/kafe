<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    // Daftar pesanan milik pelanggan yang login, terbaru di atas.
    // Tugas: Mengirim daftar pesanan milik pelanggan sebagai JSON.
    // Kenapa: Penyaringan berdasarkan akun mencegah daftar mencampur pesanan pelanggan lain.
    // Contoh: Akun dengan 2 pesanan menerima 2 ringkasan.
    public function index(Request $request)
    {
        $daftar = Pesanan::where('user_id', $request->user()->id)
            ->latest('id')
            ->get();

        return response()->json($daftar->map(fn ($p) => $this->ringkas($p))->values());
    }

    // Detail satu pesanan. Pesanan milik orang lain dianggap tidak ada (404).
    // Tugas: Mengirim rincian pesanan yang dipilih pelanggan.
    // Kenapa: Pencarian memakai id akun agar pelanggan hanya dapat melihat miliknya.
    // Contoh: ID 12 hanya tampil jika pesanan itu milik akun yang sedang masuk.
    public function show(Request $request, int $id)
    {
        $pesanan = Pesanan::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($this->bentuk($pesanan));
    }

    // Tugas: Memvalidasi lalu membuat pesanan pelanggan melalui API.
    // Kenapa: Layanan bersama memastikan pemeriksaan stok dan hitungan sama dengan Kasir.
    // Contoh: Nomor meja 3 diterima, sedangkan nomor meja 11 ditolak.
    public function store(Request $request, PesananService $layanan)
    {
        $data = $request->validate([
            'nomor_meja' => ['required', 'integer', 'between:1,10'],
            'metode_bayar' => ['required', 'in:Tunai,QRIS'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menu,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.ukuran' => ['nullable', 'in:Reguler,Large'],
        ]);

        // Satu pelanggan hanya boleh punya satu pesanan berstatus Baru.
        $adaBaru = Pesanan::where('user_id', $request->user()->id)
            ->where('status', 'Baru')
            ->exists();

        if ($adaBaru) {
            throw ValidationException::withMessages([
                'pesanan' => 'Anda masih punya pesanan berstatus Baru. Tunggu sampai diproses.',
            ]);
        }

        // Aturan menu, stok, dan biaya semuanya ada di PesananService (sama dengan Kasir).
        $pesanan = $layanan->buat(
            $request->user(),
            $data['items'],
            $data['metode_bayar'],
            (int) $data['nomor_meja'],
        );

        return response()->json($this->bentuk($pesanan), 201);
    }

    private function nomor(int $id): string
    {
        return 'A-' . str_pad($id, 3, '0', STR_PAD_LEFT);
    }

    // Ringkasan untuk daftar. Tanpa rincian item.
    private function ringkas(Pesanan $pesanan): array
    {
        return [
            'id' => $pesanan->id,
            'nomor' => $this->nomor($pesanan->id),
            'status' => $pesanan->status,
            'nomor_meja' => $pesanan->nomor_meja,
            'total' => (int) $pesanan->total,
            'dibuat' => $pesanan->created_at->toIso8601String(),
        ];
    }

    // Bentuk lengkap untuk detail dan struk. Angkanya dibaca dari yang tersimpan, bukan dihitung ulang.
    private function bentuk(Pesanan $pesanan): array
    {
        $pesanan->loadMissing('items.menu');

        return array_merge($this->ringkas($pesanan), [
            'metode_bayar' => $pesanan->metode_bayar,
            'items' => $pesanan->items->map(fn ($i) => [
                'menu' => $i->menu->nama,
                'ukuran' => $i->ukuran,
                'qty' => $i->qty,
                'harga_satuan' => (int) $i->harga_satuan,
                'subtotal' => (int) $i->harga_satuan * $i->qty,
            ])->values(),
            'subtotal' => (int) $pesanan->subtotal,
            'service' => (int) $pesanan->service,
            'pajak' => (int) $pesanan->pajak,
        ]);
    }
}