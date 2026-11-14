@extends('layouts.manajer')

@section('judul', $bahan->exists ? 'Ubah bahan' : 'Tambah bahan')

@section('isi')
<div class="max-w-[520px] rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-8 shadow-[0_3px_0_#cdbf9f]">

    <form method="POST" action="{{ $bahan->exists ? '/bahan/'.$bahan->id : '/bahan' }}">
        @csrf
        @if ($bahan->exists) @method('PUT') @endif

        <label class="text-sm font-bold text-[#6f6a5c]">Nama bahan</label>
        <input type="text" name="nama" value="{{ old('nama', $bahan->nama) }}"
               class="mt-2 h-12 w-full rounded-xl border border-[#cdbf9f] bg-[#fffdf7] px-4 text-base outline-none focus:border-[#b5452f]">
        @error('nama') <p class="mt-1 text-sm font-medium text-[#b5452f]">{{ $message }}</p> @enderror
        @error('nama_kunci') <p class="mt-1 text-sm font-medium text-[#b5452f]">{{ $message }}</p> @enderror

        <label class="mt-5 block text-sm font-bold text-[#6f6a5c]">Satuan dasar</label>
        <select name="satuan"
                class="mt-2 h-12 w-full rounded-xl border border-[#cdbf9f] bg-[#fffdf7] px-4 text-base outline-none focus:border-[#b5452f]">
            @foreach (['g', 'ml', 'pcs'] as $s)
                <option value="{{ $s }}" @selected(old('satuan', $bahan->satuan) === $s)>{{ $s }}</option>
            @endforeach
        </select>
        @error('satuan') <p class="mt-1 text-sm font-medium text-[#b5452f]">{{ $message }}</p> @enderror

        <label class="mt-5 block text-sm font-bold text-[#6f6a5c]">Batas minimum (dalam satuan dasar)</label>
        <input type="number" step="0.01" min="0" name="batas_minimum" value="{{ old('batas_minimum', $bahan->batas_minimum ?? 0) }}"
               class="mt-2 h-12 w-full rounded-xl border border-[#cdbf9f] bg-[#fffdf7] px-4 text-base outline-none focus:border-[#b5452f]">
        @error('batas_minimum') <p class="mt-1 text-sm font-medium text-[#b5452f]">{{ $message }}</p> @enderror

        <div class="mt-8 flex gap-3">
            <button type="submit" class="h-[50px] flex-1 rounded-xl bg-[#b5452f] text-base font-bold text-[#fffdf7] hover:brightness-110">Simpan</button>
            <a href="/bahan" class="flex h-[50px] items-center rounded-xl border border-[#cdbf9f] px-6 text-base font-bold text-[#6f6a5c]">Batal</a>
        </div>
    </form>
</div>
@endsection