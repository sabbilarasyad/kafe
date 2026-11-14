<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Kedai Kopi Senja</title>

    {{-- Font desain. Kalau tidak ada internet, otomatis pakai font cadangan. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700&family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="m-0 bg-[#f7f2e6] font-['Plus_Jakarta_Sans',system-ui,sans-serif] text-[#2b2a26]">
<div class="grid min-h-screen grid-cols-[44%_56%]">

    {{-- Panel kiri: hijau dengan garis kotak halus --}}
    <div class="relative flex flex-col justify-center px-[72px]"
         style="background:
            linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px) 0 0/32px 32px,
            linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px) 0 0/32px 32px,
            linear-gradient(#1f4d45, #163b35);">

        <div class="flex h-16 w-16 items-center justify-center rounded-[20px] border-[3px] border-[#f2c75c] bg-[#efe3c2]
                    font-['Fraunces',Georgia,serif] text-[26px] font-bold text-[#b5452f]">
            KS
        </div>

        <h1 class="mt-14 font-['Fraunces',Georgia,serif] text-[44px] font-bold leading-tight text-[#fffdf7]">
            Kedai Kopi Senja
        </h1>
        <p class="mt-2 text-base text-[#fffdf7]/85">
            Sistem pesanan dan stok untuk kasir &amp; manajer.
        </p>

        {{-- Tiga kotak ikon --}}
        <div class="mt-12 flex gap-[10px]">
            <div class="flex h-12 w-12 items-center justify-center rounded-[14px] border border-white"
                 style="background:linear-gradient(135deg,#fbe9b7,#f2c75c)">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#b5452f" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 9h11v5a5 5 0 0 1-5 5H10a5 5 0 0 1-5-5z"/><path d="M16 11h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M9 3v2M12 3v2"/>
                </svg>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-[14px] border border-white"
                 style="background:linear-gradient(135deg,#e9eee0,#c9d6b9)">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#b5452f" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 8h12l-1.5 12h-9z"/><path d="M12 8V3l3-1"/>
                </svg>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-[14px] border border-white"
                 style="background:linear-gradient(135deg,#f9d5cc,#e8907f)">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#b5452f" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 9h11v5a5 5 0 0 1-5 5H10a5 5 0 0 1-5-5z"/><path d="M16 11h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M9 3v2M12 3v2"/>
                </svg>
            </div>
        </div>

        {{-- Pita merah-putih di bawah --}}
        <div class="absolute bottom-0 left-0 h-[18px] w-full"
             style="background:repeating-linear-gradient(90deg,#fffdf7 0 18px,#b5452f 18px 36px)"></div>
    </div>

    {{-- Panel kanan: kartu login --}}
    <div class="flex items-center justify-center">
        <div class="w-[440px] rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-10 shadow-[0_4px_0_rgba(181,69,47,.05)]">

            <h2 class="font-['Fraunces',Georgia,serif] text-[32px] font-bold text-[#b5452f]">Masuk</h2>
            <p class="mt-1 text-sm text-[#6f6a5c]">Gunakan akun staf untuk mulai bekerja.</p>

            <form method="POST" action="/login" class="mt-8">
                @csrf

                <label class="text-sm font-bold text-[#6f6a5c]">Email</label>
                <div class="relative mt-[10px]">
                    <svg class="absolute left-[14px] top-[14px]" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6f6a5c" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="3"/><path d="M4 7l8 6 8-6"/>
                    </svg>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="kasir@kedaisenja.test"
                           class="h-12 w-full rounded-xl border border-[#cdbf9f] bg-[#fffdf7] pl-12 pr-4 text-base font-medium outline-none focus:border-[#b5452f]">
                </div>

                <label class="mt-5 block text-sm font-bold text-[#6f6a5c]">Password</label>
                <div class="relative mt-[10px]">
                    <svg class="absolute left-[14px] top-[14px]" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6f6a5c" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="10" width="14" height="10" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    </svg>
                    <input type="password" name="password" placeholder="••••••••"
                           class="h-12 w-full rounded-xl border border-[#cdbf9f] bg-[#fffdf7] pl-12 pr-4 text-base font-medium outline-none focus:border-[#b5452f]">
                </div>

                @error('email')
                    <p class="mt-3 text-sm font-medium text-[#b5452f]">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="mt-8 h-[50px] w-full rounded-xl text-base font-bold text-[#fffdf7] shadow-[0_4px_0_rgba(181,69,47,.1)] hover:brightness-110"
                        style="background:linear-gradient(#c75a44,#b5452f)">
                    Masuk
                </button>

                <p class="mt-6 text-center text-sm font-medium text-[#6f6a5c]">Akun dibuat oleh manajer</p>
            </form>
        </div>
    </div>
</div>
</body>
</html>