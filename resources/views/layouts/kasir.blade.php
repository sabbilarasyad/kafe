<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') - Kedai Kopi Senja</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700&family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="m-0 flex min-h-screen bg-[#f7f2e6] font-['Plus_Jakarta_Sans',system-ui,sans-serif] text-[#2b2a26]">

    {{-- Sidebar --}}
    <aside class="relative flex w-[248px] shrink-0 flex-col px-[14px] pb-[70px] pt-[18px]"
           style="background:
              linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px) 0 0/32px 32px,
              linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px) 0 0/32px 32px,
              linear-gradient(#1f4d45, #163b35);">

        <div class="rounded-[18px] border-2 border-[#f2c75c] bg-[#b5452f] p-[6px]">
            <div class="rounded-[14px] border border-white/40 px-4 py-4 text-center font-['Fraunces',Georgia,serif] text-2xl font-bold leading-tight text-[#2b2a26]">
                Kedai Kopi Senja
            </div>
        </div>

        <p class="mb-3 mt-5 px-2 text-sm font-bold text-[#f2c75c]">MENU</p>

        @php
            $menu = [
                ['Buat pesanan', '/pesanan/create', 'pesanan/create*'],
                ['Daftar pesanan', '/pesanan', 'pesanan'],
                ['Reset password pelanggan', '/reset-password', 'reset-password*'],
                ['Ganti password', '/ganti-password', 'ganti-password*'],
            ];
        @endphp

        <nav class="flex flex-col gap-2">
            @foreach ($menu as [$nama, $url, $pola])
                @if (request()->is($pola))
                    <a href="{{ $url }}" class="rounded-xl border-2 border-[#f2c75c] bg-[#fffdf7] px-4 py-3 text-sm font-bold text-[#b5452f] shadow-[0_4px_0_#b5452f]">{{ $nama }}</a>
                @else
                    <a href="{{ $url }}" class="rounded-xl px-4 py-3 text-sm font-medium text-[#fffdf7] hover:bg-white/10">{{ $nama }}</a>
                @endif
            @endforeach
        </nav>

        {{-- Kartu pengguna --}}
        <div class="mt-auto flex items-center gap-3 rounded-2xl border border-[#f2c75c] bg-[#2d6358] px-4 py-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#b5452f] text-base font-bold text-[#fffdf7]">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-base font-bold leading-tight text-[#fffdf7]">{{ auth()->user()->name }}</p>
                <p class="text-sm font-bold capitalize text-[#f2c75c]">{{ auth()->user()->role }}</p>
            </div>
        </div>

        {{-- Pola kotak-kotak di bawah --}}
        <div class="absolute bottom-0 left-0 h-[54px] w-full"
             style="background:repeating-conic-gradient(#2b2a26 0% 25%, #fffdf7 0% 50%) 0 0/36px 36px"></div>
    </aside>

    {{-- Konten --}}
    <div class="flex min-w-0 flex-1 flex-col">

        <header class="flex h-[84px] items-center justify-between bg-[#b5452f] px-[16px]">
            <h1 class="rounded-xl border-[3px] border-[#f2c75c] bg-[#fffdf7] px-6 py-2 font-['Fraunces',Georgia,serif] text-[28px] font-bold text-[#b5452f]">
                @yield('judul')
            </h1>
            <div class="flex items-center gap-5">
                <span class="text-base font-bold capitalize text-[#fffdf7]">{{ auth()->user()->role }}</span>
                <form method="POST" action="/logout">
                    @csrf
                    <button class="rounded-xl bg-[#fffdf7] px-8 py-[10px] text-base font-bold text-[#b5452f] hover:brightness-95">Keluar</button>
                </form>
            </div>
        </header>
        <div class="h-2" style="background:repeating-linear-gradient(90deg,#fffdf7 0 8px,transparent 8px 16px)"></div>

        <main class="p-8"
              style="background:
                 linear-gradient(rgba(181,69,47,.05) 1px, transparent 1px) 0 0/32px 32px,
                 linear-gradient(90deg, rgba(181,69,47,.05) 1px, transparent 1px) 0 0/32px 32px;">

            @if (session('status'))
                <div class="mb-4 rounded-xl bg-[#e9eee0] px-5 py-3 text-sm font-bold text-[#2A6B45]">
                    {{ session('status') }}
                </div>
            @endif

            @yield('isi')
        </main>
    </div>
</body>
</html>