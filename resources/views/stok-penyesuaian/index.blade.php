@extends('layouts.manajer')

@section('judul', 'Penyesuaian stok')

@section('isi')
<div class="grid grid-cols-2 gap-5">
    <div class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] px-6 py-5 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-[20px] font-bold">Catat penyesuaian</h2>
        <p class="mt-1 text-sm text-[#6f6a5c]">Selisih negatif untuk bahan rusak, positif untuk hasil opname. Alasan wajib diisi.</p>

        @if (session('status'))
            <div class="mt-3 rounded-xl bg-[#e3efe6] px-4 py-2 text-sm font-bold text-[#2A6B45]">{{ session('status') }}</div>
        @endif

        <form method="POST" action="/stok-penyesuaian" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold text-[#6f6a5c]">Bahan</label>
                <select name="bahan_id" class="mt-1 w-full rounded-xl border border-[#cdbf9f] bg-white px-3 py-2">
                    @foreach ($bahan as $b)
                        <option value="{{ $b->id }}" @selected(old('bahan_id') == $b->id)>{{ $b->nama }} (stok {{ rtrim(rtrim(number_format($b->stok, 2, ',', '.'), '0'), ',') }} {{ $b->satuan }})</option>
                    @endforeach
                </select>
                @error('bahan_id') <p class="mt-1 text-sm text-[#8E2A20]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-[#6f6a5c]">Selisih</label>
                <input type="number" step="0.01" name="selisih" value="{{ old('selisih') }}"
                       class="mt-1 w-full rounded-xl border border-[#cdbf9f] bg-white px-3 py-2">
                @error('selisih') <p class="mt-1 text-sm text-[#8E2A20]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-[#6f6a5c]">Alasan</label>
                <input type="text" name="alasan" value="{{ old('alasan') }}" maxlength="255"
                       class="mt-1 w-full rounded-xl border border-[#cdbf9f] bg-white px-3 py-2">
                @error('alasan') <p class="mt-1 text-sm text-[#8E2A20]">{{ $message }}</p> @enderror
            </div>
            <button type="submit"
                    class="w-full rounded-2xl bg-[#b5452f] py-3 font-bold text-[#fffdf7] shadow-[0_4px_0_#8c3322]">
                Simpan penyesuaian
            </button>
        </form>
    </div>

    <div class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] px-6 py-5 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-[20px] font-bold">Riwayat terbaru</h2>
        <table class="mt-4 w-full text-left text-sm">
            <thead>
                <tr class="text-[#6f6a5c]">
                    <th class="py-2">Waktu</th>
                    <th>Bahan</th>
                    <th>Selisih</th>
                    <th>Alasan</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $r)
                    <tr class="border-t border-[#e6dcc0]">
                        <td class="py-2">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $r->bahan->nama }}</td>
                        <td class="font-bold">{{ $r->selisih > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($r->selisih, 2, ',', '.'), '0'), ',') }} {{ $r->bahan->satuan }}</td>
                        <td>{{ $r->alasan }}</td>
                        <td>{{ $r->user->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-[#6f6a5c]">Belum ada penyesuaian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection