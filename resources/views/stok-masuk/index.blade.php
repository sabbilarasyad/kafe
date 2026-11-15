@extends('layouts.manajer')

@section('judul', 'Stok masuk')

@section('isi')
<div class="flex items-start gap-8">

    {{-- Kiri: form pembelian --}}
    <section class="w-[390px] shrink-0 rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-xl font-bold">Catat pembelian</h2>

        @if ($errors->any())
            <div class="mt-3 rounded-xl bg-[#f9d5cc] px-4 py-2 text-sm font-bold text-[#8E2A20]">
                @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <form method="POST" action="/stok-masuk" class="mt-3">
            @csrf

            <label class="text-sm font-bold text-[#6f6a5c]">Tanggal</label>
            <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}"
                   class="mt-1 h-11 w-full rounded-xl border border-[#cdbf9f] px-4 outline-none focus:border-[#b5452f]">

            <label class="mt-4 block text-sm font-bold text-[#6f6a5c]">Bahan</label>
            <select name="bahan_id" class="mt-1 h-11 w-full rounded-xl border border-[#cdbf9f] bg-[#fffdf7] px-4 outline-none focus:border-[#b5452f]">
                <option value="">Pilih bahan</option>
                @foreach ($bahan as $b)
                    <option value="{{ $b->id }}" @selected(old('bahan_id') == $b->id)>{{ $b->nama }} ({{ $b->satuan }})</option>
                @endforeach
            </select>

            <label class="mt-4 block text-sm font-bold text-[#6f6a5c]">Jumlah (satuan dasar bahan)</label>
            <input type="number" step="0.01" min="0.01" name="jumlah" value="{{ old('jumlah') }}"
                   class="mt-1 h-11 w-full rounded-xl border border-[#cdbf9f] px-4 outline-none focus:border-[#b5452f]">

            <button type="submit" class="mt-8 h-[50px] w-full rounded-xl font-bold text-[#fffdf7] hover:brightness-110"
                    style="background:linear-gradient(#c75a44,#b5452f)">Simpan pembelian</button>
        </form>
    </section>

    {{-- Kanan: riwayat --}}
    <section class="min-w-0 flex-1 overflow-hidden rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] shadow-[0_3px_0_#cdbf9f]">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-[#f7f2e6] text-sm font-bold text-[#6f6a5c]">
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="py-3">Bahan</th>
                    <th class="py-3 text-right">Jumlah</th>
                    <th class="py-3 pl-8">Dicatat oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $r)
                    <tr class="border-t border-[#e6dcc0]">
                        <td class="px-5 py-4">{{ $r->tanggal->format('d M Y') }}</td>
                        <td>{{ $r->bahan->nama }}</td>
                        <td class="text-right font-bold">+{{ rtrim(rtrim(number_format($r->jumlah, 2, ',', '.'), '0'), ',') }} {{ $r->bahan->satuan }}</td>
                        <td class="pl-8">{{ $r->user->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-[#6f6a5c]">Belum ada pembelian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection