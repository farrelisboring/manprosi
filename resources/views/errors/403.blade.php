<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>403 Forbidden | Hospital Asset Manager</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#eef1f7] text-slate-950 antialiased">
        <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid w-full max-w-6xl gap-8 rounded-[32px] bg-white p-6 shadow-[0_32px_100px_rgba(15,23,42,0.12)] ring-1 ring-slate-200/70 lg:grid-cols-[320px_minmax(0,1fr)] lg:p-8">
                <aside class="rounded-[28px] bg-[#33457f] px-6 py-8 text-white">
                    <div class="flex items-center gap-4">
                        <span class="grid h-16 w-16 place-items-center rounded-[22px] bg-white/10 ring-1 ring-white/15">
                            <svg aria-hidden="true" class="h-11 w-11" fill="none" viewBox="0 0 64 64">
                                <path d="M32 6L49.5 13.5V29C49.5 40.5 42.4 50.9 32 56C21.6 50.9 14.5 40.5 14.5 29V13.5L32 6Z" fill="#F8FAFF" stroke="#9AB1F5" stroke-width="3"/>
                                <path d="M28 19.5C28 18.6716 28.6716 18 29.5 18H34.5C35.3284 18 36 18.6716 36 19.5V27H43.5C44.3284 27 45 27.6716 45 28.5V33.5C45 34.3284 44.3284 35 43.5 35H36V42.5C36 43.3284 35.3284 44 34.5 44H29.5C28.6716 44 28 43.3284 28 42.5V35H20.5C19.6716 35 19 34.3284 19 33.5V28.5C19 27.6716 19.6716 27 20.5 27H28V19.5Z" fill="#33457f"/>
                            </svg>
                        </span>

                        <div>
                            <p class="text-xl font-black tracking-tight">RS Mitra Husada</p>
                            <p class="mt-1 text-sm text-white/70">Hospital Asset Monitoring System</p>
                        </div>
                    </div>

                    <div class="mt-12">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-white/60">Akses Ditolak</p>
                        <h1 class="mt-3 text-4xl font-black tracking-tight">403 Forbidden</h1>
                        <p class="mt-4 text-base leading-7 text-white/80">
                            Halaman ini mengikuti gaya visual sistem utama, tetapi akses ke kontennya sedang dibatasi.
                        </p>
                    </div>
                </aside>

                <section class="flex items-center">
                    <div class="w-full rounded-[28px] bg-[#f7f9fe] px-8 py-10 ring-1 ring-slate-200 sm:px-10">
                        <span class="inline-flex rounded-full bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-700">403</span>
                        <h2 class="mt-5 text-4xl font-black tracking-tight text-slate-950">Anda tidak memiliki izin untuk membuka halaman ini.</h2>
                        <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">
                            Saat role-based guard nanti ditambahkan, tampilan ini akan menjadi fallback yang rapi ketika pengguna mencoba membuka modul di luar hak aksesnya.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a class="rounded-full bg-slate-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" href="{{ route('dashboard') }}">
                                Kembali ke Dashboard
                            </a>
                            <a class="rounded-full border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-950" href="{{ route('login') }}">
                                Buka Halaman Login
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
