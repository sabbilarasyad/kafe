@extends('layouts.manajer')

@section('judul', 'Menu & resep')

@section('isi')
<div class="max-w-[520px] rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-8 shadow-[0_3px_0_#cdbf9f]">
    <h2 class="text-xl font-bold">
        {{ $item->exists ? 'Ubah takaran' : 'Tambah bahan' }}: {{ $menu->nama }}
    </h2>

    @if ($errors->any())
        <div class="mt-3 rounded-xl bg-[#f9d5cc] px-4 py-2 text-sm font-bold text-[#8E2A20]">
            @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ $item->exists ? '/resep/' . $item->id : '/menu/' . $menu->id . '/resep' }}" class="mt-4">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <label class="text-sm font-bold text-[#6f6a5c]">Bahan</label>
        @if ($item->exists)
            <p class="mt-1 flex h-11 items-center rounded-xl bg-[#f7f2e6] px-4">{{ $item->bahan->nama }} ({{ $item->bahan->satuan }})</p>
        @else
            <select name="bahan_id" class="mt-1 h-11 w-full rounded-xl border border-[#cdbf9f] bg-[#fffdf7] px-4 outline-none focus:border-[#b5452f]">
                <option value="">Pilih bahan</option>
                @foreach ($bahan as $b)
                    <option value="{{ $b->id }}" @selected(old('bahan_id') == $b->id)>{{ $b->nama }} ({{ $b->satuan }})</option>
                @endforeach
            </select>
        @endif

        <label class="mt-5 block text-sm font-bold text-[#6f6a5c]">Takaran (dalam satuan dasar bahan)</label>
        <input type="number" step="0.01" min="0.01" name="takaran" value="{{ old('takaran', $item->takaran) }}"
               class="mt-1 h-11 w-full rounded-xl border border-[#cdbf9f] px-4 outline-none focus:border-[#b5452f]">

        <div class="mt-8 flex gap-3">
            <button type="submit" class="h-[50px] flex-1 rounded-xl bg-[#b5452f] font-bold text-[#fffdf7] hover:brightness-110">Simpan</button>
            <a href="/menu/{{ $menu->id }}" class="flex h-[50px] items-center rounded-xl border border-[#cdbf9f] px-6 font-bold text-[#6f6a5c]">Batal</a>
        </div>
    </form>
</div>
@endsection