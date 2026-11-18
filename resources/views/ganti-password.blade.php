@extends(auth()->user()->role === 'manajer' ? 'layouts.manajer' : 'layouts.kasir')

@section('judul', 'Ganti password')

@section('isi')
<div class="max-w-md rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] px-8 py-7 shadow-[0_3px_0_#cdbf9f]">
    <h2 class="text-[22px] font-bold text-[#2b2a26]">Ganti password</h2>
    <p class="mt-1 text-sm text-[#6f6a5c]">Password baru minimal 8 karakter.</p>

    @if (session('status'))
        <div class="mt-4 rounded-2xl border border-[#1f4d45] bg-[#e3efe6] px-4 py-3 text-sm font-bold text-[#1f4d45]">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('ganti-password') }}" class="mt-5 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-bold text-[#6f6a5c]">Password lama</label>
            <input type="password" name="password_lama"
                   class="mt-1 w-full rounded-xl border border-[#cdbf9f] bg-white px-4 py-2.5 text-[#2b2a26] focus:border-[#b5452f] focus:outline-none">
            @error('password_lama')
                <p class="mt-1 text-sm text-[#8E2A20]">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-[#6f6a5c]">Password baru</label>
            <input type="password" name="password_baru"
                   class="mt-1 w-full rounded-xl border border-[#cdbf9f] bg-white px-4 py-2.5 text-[#2b2a26] focus:border-[#b5452f] focus:outline-none">
            @error('password_baru')
                <p class="mt-1 text-sm text-[#8E2A20]">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-[#6f6a5c]">Ulangi password baru</label>
            <input type="password" name="password_baru_confirmation"
                   class="mt-1 w-full rounded-xl border border-[#cdbf9f] bg-white px-4 py-2.5 text-[#2b2a26] focus:border-[#b5452f] focus:outline-none">
        </div>

        <button type="submit"
                class="w-full rounded-2xl bg-[#b5452f] py-3 font-bold text-[#fffdf7] shadow-[0_4px_0_#8c3322]">
            Simpan password
        </button>
    </form>
</div>
@endsection