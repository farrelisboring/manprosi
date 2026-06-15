<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login | Hospital Asset Manager</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $hospitalLayoutIllustration = file_get_contents(base_path('ICON/login page hospital layout.svg'));
    @endphp
    <body class="min-h-screen bg-[#dfe5f3] text-slate-950 antialiased">
        <main class="mx-auto flex min-h-screen max-w-[1600px] items-center px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid w-full gap-8 rounded-[28px] bg-[#e7ecf7] p-6 shadow-[0_30px_90px_rgba(51,69,127,0.18)] ring-1 ring-white/60 lg:grid-cols-[1.05fr_0.95fr] lg:p-8 xl:min-h-[780px]">
                <section class="relative overflow-hidden rounded-[28px] bg-transparent px-4 py-4 lg:px-6 lg:py-6">
                    <div class="flex items-center gap-4">
                        <span class="grid h-16 w-16 place-items-center rounded-[22px] bg-[#edf2ff] ring-1 ring-[#c7d3f4]">
                            <svg aria-hidden="true" class="h-11 w-11" fill="none" viewBox="0 0 64 64">
                                <path d="M32 6L49.5 13.5V29C49.5 40.5 42.4 50.9 32 56C21.6 50.9 14.5 40.5 14.5 29V13.5L32 6Z" fill="#F8FAFF" stroke="#5A76BF" stroke-width="3"/>
                                <path d="M28 19.5C28 18.6716 28.6716 18 29.5 18H34.5C35.3284 18 36 18.6716 36 19.5V27H43.5C44.3284 27 45 27.6716 45 28.5V33.5C45 34.3284 44.3284 35 43.5 35H36V42.5C36 43.3284 35.3284 44 34.5 44H29.5C28.6716 44 28 43.3284 28 42.5V35H20.5C19.6716 35 19 34.3284 19 33.5V28.5C19 27.6716 19.6716 27 20.5 27H28V19.5Z" fill="#33457f"/>
                            </svg>
                        </span>

                        <div>
                            <p class="text-2xl font-black tracking-tight text-slate-700">RS Mitra Husada</p>
                            <p class="text-sm font-semibold uppercase tracking-[0.08em] text-slate-500">Melayani dengan hati</p>
                        </div>
                    </div>

                    <div class="mt-14 max-w-[520px]">
                        <h1 class="text-4xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl">
                            Hospital Asset Monitoring System
                        </h1>
                        <p class="mt-5 text-xl leading-10 text-slate-700">
                            Sistem terintegrasi untuk Pengelolaan, Pelacakan, dan Monitoring Aset Rumah Sakit
                        </p>
                    </div>

                    <div class="mt-12 max-w-[760px] xl:mt-16">
                        <div class="[&>svg]:h-auto [&>svg]:w-full">
                            {!! $hospitalLayoutIllustration !!}
                        </div>
                    </div>
                </section>

                <section class="flex items-center justify-center">
                    <div class="w-full max-w-[620px] rounded-[28px] bg-white px-8 py-10 shadow-[0_22px_60px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/80 sm:px-12 sm:py-12">
                        <div class="text-center">
                            <h2 class="text-4xl font-black tracking-tight text-slate-700">Selamat Datang !!!</h2>
                            <p class="mt-2 text-base text-slate-500">Silahkan login untuk melanjutkan</p>
                        </div>

                        <form class="mt-12 space-y-7" action="{{ route('login.store') }}" method="POST">
                            @csrf

                            @if ($errors->any())
                                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-semibold text-slate-900" for="login_identity">Email atau Username</label>
                                <div class="mt-3 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24">
                                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                        <path d="M4 20C4.85038 17.0144 7.59873 15 12 15C16.4013 15 19.1496 17.0144 20 20" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                    </svg>
                                    <input class="w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0" id="login_identity" name="login_identity" placeholder="Masukkan email atau username" type="text" value="{{ old('login_identity') }}">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-900" for="password">Password</label>
                                <div class="mt-3 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24">
                                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                        <path d="M4 20C4.85038 17.0144 7.59873 15 12 15C16.4013 15 19.1496 17.0144 20 20" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                    </svg>
                                    <input class="w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0" id="password" name="password" placeholder="Masukkan password" type="password">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24">
                                        <path d="M3 3L21 21" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                        <path d="M10.58 10.58C10.21 10.95 10 11.46 10 12C10 13.1 10.9 14 12 14C12.54 14 13.05 13.79 13.42 13.42" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                        <path d="M16.68 16.69C15.19 17.44 13.61 17.82 12 17.82C7.91 17.82 4.14 15.36 1.5 11.18C2.54 9.53 3.78 8.17 5.16 7.12M9.88 6.18C10.58 6.06 11.29 6 12 6C16.09 6 19.86 8.46 22.5 12.64C21.73 13.86 20.86 14.93 19.91 15.84" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                    </svg>
                                </div>
                            </div>

                            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700">
                                <input class="h-5 w-5 rounded border-slate-300 text-[#4b61af] focus:ring-[#4b61af]" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                                <span>Ingat saya</span>
                            </label>

                            <button class="w-full rounded-2xl bg-[#4b61af] px-6 py-4 text-3xl font-black tracking-tight text-white transition hover:bg-[#3f5399]" type="submit">
                                Login
                            </button>

                            <div class="mt-6 text-center text-sm text-slate-600">
                                Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-[#4b61af] hover:underline">Daftar di sini</a>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
