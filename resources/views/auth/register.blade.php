<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Register | Hospital Asset Manager</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $hospitalLayoutIllustration = file_get_contents(base_path('ICON/login page hospital layout.svg'));
    @endphp
    <body class="min-h-screen bg-[#dfe5f3] text-slate-950 antialiased">
        <main class="mx-auto flex min-h-screen max-w-[1600px] items-center px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid w-full gap-8 rounded-[28px] bg-[#e7ecf7] p-6 shadow-[0_30px_90px_rgba(51,69,127,0.18)] ring-1 ring-white/60 lg:grid-cols-[1.05fr_0.95fr] lg:p-8 xl:min-h-[780px]">
                <section class="relative hidden overflow-hidden rounded-[28px] bg-transparent px-4 py-4 lg:block lg:px-6 lg:py-6">
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
                    <div class="w-full max-w-[620px] rounded-[28px] bg-white px-8 py-8 shadow-[0_22px_60px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/80 sm:px-12 sm:py-10">
                        <div class="text-center">
                            <h2 class="text-3xl font-black tracking-tight text-slate-700">Buat Akun Baru</h2>
                            <p class="mt-2 text-base text-slate-500">Silahkan isi data untuk mendaftar</p>
                        </div>

                        <form class="mt-8 space-y-5" action="{{ route('register.store') }}" method="POST">
                            @csrf

                            @if ($errors->any())
                                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-semibold text-slate-900" for="name">Nama Lengkap</label>
                                <div class="mt-2 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm focus-within:border-[#4b61af] focus-within:ring-1 focus-within:ring-[#4b61af]">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"/>
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z"/>
                                    </svg>
                                    <input class="w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0" id="name" name="name" placeholder="Masukkan nama lengkap" type="text" value="{{ old('name') }}" required autofocus autocomplete="name">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-900" for="email">Email</label>
                                <div class="mt-2 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm focus-within:border-[#4b61af] focus-within:ring-1 focus-within:ring-[#4b61af]">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24">
                                        <path d="M4 7.00005L10.2 11.65C11.2667 12.45 12.7333 12.45 13.8 11.65L20 7" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                        <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                    </svg>
                                    <input class="w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0" id="email" name="email" placeholder="Masukkan email aktif" type="email" value="{{ old('email') }}" required autocomplete="username">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900" for="password">Password</label>
                                    <div class="mt-2 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm focus-within:border-[#4b61af] focus-within:ring-1 focus-within:ring-[#4b61af]">
                                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24">
                                            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                            <path d="M4 20C4.85038 17.0144 7.59873 15 12 15C16.4013 15 19.1496 17.0144 20 20" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                        </svg>
                                        <input class="w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0" id="password" name="password" placeholder="Password" type="password" required autocomplete="new-password">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-900" for="password_confirmation">Konfirmasi</label>
                                    <div class="mt-2 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm focus-within:border-[#4b61af] focus-within:ring-1 focus-within:ring-[#4b61af]">
                                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24">
                                            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                            <path d="M4 20C4.85038 17.0144 7.59873 15 12 15C16.4013 15 19.1496 17.0144 20 20" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                                        </svg>
                                        <input class="w-full border-0 bg-transparent p-0 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0" id="password_confirmation" name="password_confirmation" placeholder="Ulangi" type="password" required autocomplete="new-password">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-900 mb-2">Pilih Peran (Role)</label>
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach($roles as $role)
                                        @if($role->value !== 'administrator')
                                        <label class="relative flex cursor-pointer rounded-2xl border bg-white p-3 shadow-sm hover:bg-slate-50 focus:outline-none has-[:checked]:border-[#4b61af] has-[:checked]:bg-[#edf2ff] has-[:checked]:ring-1 has-[:checked]:ring-[#4b61af]">
                                            <input type="radio" name="role" value="{{ $role->value }}" class="sr-only" required @checked(old('role') == $role->value)>
                                            <span class="flex flex-col text-center w-full">
                                                <span class="text-sm font-bold text-slate-900 capitalize">{{ $role->value }}</span>
                                            </span>
                                        </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <button class="w-full mt-4 rounded-2xl bg-[#4b61af] px-6 py-4 text-2xl font-black tracking-tight text-white transition hover:bg-[#3f5399]" type="submit">
                                Daftar Sekarang
                            </button>

                            <div class="mt-6 text-center text-sm text-slate-600">
                                Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-[#4b61af] hover:underline">Login di sini</a>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
