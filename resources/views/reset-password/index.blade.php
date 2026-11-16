@extends('layouts.kasir')

@section('judul', 'Reset password pelanggan')

@section('isi')
<div class="flex items-start gap-6">

    {{-- Kiri: cari pelanggan --}}
    <section class="w-[500px] rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-xl font-bold">Cari pelanggan</h2>

        <form method="GET" action="/reset-password" class="mt-3">
            <label class="text-sm font-bold text-[#6f6a5c]">Nomor HP pelanggan</label>
            <input type="text" name="nomor_hp" value="{{ request('nomor_hp') }}" placeholder="0812xxxxxxxx"
                   class="mt-1 block h-11 w-full rounded-xl border border-[#cdbf9f] px-4 outline-none focus:border-[#b5452f]">
            <button type="submit" class="mt-3 rounded-full border border-[#b5452f] px-8 py-2 font-bold text-[#b5452f] hover:bg-[#b5452f] hover:text-[#fffdf7]">Cari</button>
        </form>

        @if ($pelanggan)
            <div class="mt-5 rounded-2xl bg-[#f7f2e6] px-5 py-4">
                <p class="text-lg font-bold">{{ $pelanggan->name }}</p>
                <p class="text-[#6f6a5c]">Pelanggan terdaftar · {{ $pelanggan->nomor_hp }}</p>
                <p class="text-sm font-bold text-[#6f6a5c]">Terdaftar {{ $pelanggan->created_at->format('d M Y') }}</p>
            </div>

            <form method="POST" action="/reset-password" class="mt-5">
                @csrf
                <input type="hidden" name="nomor_hp" value="{{ $pelanggan->nomor_hp }}">
                <button type="submit" class="h-[50px] w-full rounded-xl font-bold text-[#fffdf7] hover:brightness-110"
                        style="background:linear-gradient(#c75a44,#b5452f)">Reset ke password sementara</button>
            </form>
        @elseif ($dicari)
            <p class="mt-5 rounded-xl bg-[#f9d5cc] px-4 py-3 text-sm font-bold text-[#8E2A20]">Pelanggan dengan nomor itu tidak ditemukan.</p>
        @endif
    </section>

    {{-- Kanan: password sementara (tampil sekali) --}}
    <section class="w-[310px] rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-xl font-bold">Password sementara</h2>

        @if (session('sementara'))
            <p id="sementara" class="mt-4 rounded-2xl bg-[#f0e3c3] px-5 py-4 font-mono text-xl font-bold tracking-wider text-[#b5452f]">{{ session('sementara') }}</p>
            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('sementara').textContent.trim())"
                    class="mt-3 w-full rounded-full border border-[#b5452f] py-2 font-bold text-[#b5452f] hover:bg-[#b5452f] hover:text-[#fffdf7]">Salin</button>
            <p class="mt-3 text-sm text-[#6f6a5c]">Berikan ke pelanggan langsung.<br>Wajib ganti saat login berikutnya.<br>Password ini tidak akan tampil lagi.</p>
        @else
            <p class="mt-4 text-[#6f6a5c]">Belum ada. Cari pelanggan lalu tekan reset.</p>
        @endif
    </section>
</div>
@endsection