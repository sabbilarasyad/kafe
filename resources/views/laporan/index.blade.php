@extends('layouts.manajer')

@section('judul', 'Laporan')

@section('isi')
@php
    $angka = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
@endphp

{{-- Pilih periode --}}
<form method="GET" action="/laporan" class="flex items-end gap-4">
    <div>
        <label class="text-sm font-bold text-[#6f6a5c]">Dari</label>
        <input type="date" name="dari" value="{{ $dari->toDateString() }}"
               class="mt-1 block h-11 rounded-xl border border-[#cdbf9f] bg-[#fffdf7] px-4 outline-none focus:border-[#b5452f]">
    </div>
    <div>
        <label class="text-sm font-bold text-[#6f6a5c]">Sampai</label>
        <input type="date" name="sampai" value="{{ $sampai->toDateString() }}"
               class="mt-1 block h-11 rounded-xl border border-[#cdbf9f] bg-[#fffdf7] px-4 outline-none focus:border-[#b5452f]">
    </div>
    <button type="submit" class="h-11 rounded-xl px-8 font-bold text-[#fffdf7] hover:brightness-110"
            style="background:linear-gradient(#c75a44,#b5452f)">Terapkan</button>
</form>

@if ($errors->any())
    <p class="mt-3 text-sm font-bold text-[#8E2A20]">{{ $errors->first() }}</p>
@endif

<div class="mt-5 grid grid-cols-[1.6fr_1fr] gap-6">

    {{-- Penjualan per menu --}}
    <section class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-xl font-bold">Penjualan per menu</h2>
        <table class="mt-4 w-full text-left">
            <thead>
                <tr class="bg-[#f7f2e6] text-sm font-bold text-[#6f6a5c]">
                    <th class="px-3 py-2">Menu</th>
                    <th class="py-2">Qty</th>
                    <th class="py-2">Omzet</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($penjualan as $p)
                    <tr class="border-t border-[#e6dcc0]">
                        <td class="px-3 py-3">{{ $p->nama }}</td>
                        <td>{{ $p->qty }}</td>
                        <td>Rp{{ number_format($p->omzet, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-3 py-6 text-center text-[#6f6a5c]">Belum ada penjualan di periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    {{-- Bahan menipis --}}
    <section class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-xl font-bold">Bahan menipis</h2>
        <div class="mt-4 space-y-3">
            @forelse ($menipis as $b)
                <div class="flex items-center justify-between rounded-xl bg-[#f7f2e6] px-4 py-3">
                    <div>
                        <p class="font-bold">{{ $b->nama }}</p>
                        <p class="text-sm font-bold text-[#6f6a5c]">Sisa {{ $angka($b->stok) }} {{ $b->satuan }} · batas {{ $angka($b->batas_minimum) }} {{ $b->satuan }}</p>
                    </div>
                    <span class="rounded-full bg-[#fbe9b7] px-3 py-1 text-sm font-bold text-[#8A6100]">Menipis</span>
                </div>
            @empty
                <p class="text-[#6f6a5c]">Tidak ada bahan yang menipis.</p>
            @endforelse
        </div>
    </section>
</div>

{{-- Margin per menu --}}
<section class="mt-6 rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
    <h2 class="text-xl font-bold">Margin per menu</h2>
    <table class="mt-4 w-full text-left">
        <thead>
            <tr class="bg-[#f7f2e6] text-sm font-bold text-[#6f6a5c]">
                <th class="px-3 py-2">Menu</th>
                <th class="py-2">Harga jual</th>
                <th class="py-2 pr-3 text-right">Margin</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($margin as $m)
                <tr class="border-t border-[#e6dcc0]">
                    <td class="px-3 py-3">{{ $m['nama'] }}</td>
                    <td>Rp{{ number_format($m['harga'], 0, ',', '.') }}</td>
                    <td class="pr-3 text-right font-bold">Rp{{ number_format($m['margin'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-3 py-6 text-center text-[#6f6a5c]">Belum ada menu yang margin-nya bisa dihitung.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection