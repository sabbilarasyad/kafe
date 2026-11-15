@extends('layouts.manajer')

@section('judul', 'HPP & margin')

@section('isi')
@php
    $tanggal = $diperbarui ? \Carbon\Carbon::parse($diperbarui)->format('d M Y H:i') : null;
@endphp

{{-- Banner: Supplier berhasil atau gagal --}}
@if ($berhasil)
    <p class="mb-4 font-bold text-[#6f6a5c]">
        Harga bahan dari Supplier, diperbarui {{ $tanggal ?? '-' }}
    </p>
@else
    <div class="mb-4 rounded-2xl border border-[#f2c75c] bg-[#fdeec6] px-6 py-3">
        <p class="font-bold text-[#8A6100]">Layanan Supplier tidak dapat dihubungi</p>
        <p class="text-sm text-[#8A6100]">
            @if ($tanggal)
                Memakai harga terakhir yang tersimpan, diperbarui {{ $tanggal }}.
            @else
                Belum ada harga tersimpan.
            @endif
        </p>
    </div>
@endif

{{-- Tiga kartu ringkasan --}}
<div class="grid grid-cols-3 gap-5">
    <div class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] px-6 py-5 shadow-[0_3px_0_#cdbf9f]">
        <p class="font-bold text-[#6f6a5c]">Rata-rata margin</p>
        <p class="mt-1 text-[32px] font-bold">Rp{{ number_format($rata, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] px-6 py-5 shadow-[0_3px_0_#cdbf9f]">
        <p class="font-bold text-[#6f6a5c]">HPP tertinggi</p>
        <p class="mt-1 text-[32px] font-bold">Rp{{ number_format($tertinggi, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] px-6 py-5 shadow-[0_3px_0_#cdbf9f]">
        <p class="font-bold text-[#6f6a5c]">Menu dihitung</p>
        <p class="mt-1 text-[32px] font-bold">{{ $jumlah }} menu</p>
    </div>
</div>

{{-- Tabel --}}
<div class="mt-6 overflow-hidden rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] shadow-[0_3px_0_#cdbf9f]">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-[#f7f2e6] text-sm font-bold text-[#6f6a5c]">
                <th class="px-5 py-3">Menu</th>
                <th class="py-3">Harga jual</th>
                <th class="py-3">HPP</th>
                <th class="py-3 text-right">Margin</th>
                <th class="py-3 pl-8">Margin %</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($baris as $b)
                <tr class="border-t border-[#e6dcc0]">
                    <td class="px-5 py-4">{{ $b['nama'] }}</td>
                    <td>Rp{{ number_format($b['harga'], 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($b['hpp'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp{{ number_format($b['margin'], 0, ',', '.') }}</td>
                    <td class="pl-8">
                        <span class="rounded-full bg-[#e3efe6] px-3 py-1 text-[13px] font-bold text-[#2A6B45]">{{ $b['persen'] }}%</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-[#6f6a5c]">Belum ada menu yang bisa dihitung.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="mt-4 text-[#6f6a5c]">HPP = takaran × harga per satuan dasar bahan. Margin = harga jual − HPP.</p>

@if (count($dilewati))
    <p class="mt-2 text-sm text-[#8E2A20]">
        Belum bisa dihitung (belum ada resep atau ada bahan tanpa harga): {{ implode(', ', $dilewati) }}.
    </p>
@endif
@endsection