<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Hospital Asset Manager')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $navItems = [
            [
                'label' => 'Dashboard',
                'route' => route('dashboard'),
                'active' => request()->routeIs('dashboard'),
                'icon' => file_get_contents(base_path('ICON/Dashboard Icon.svg')),
            ],
            [
                'label' => 'Pencarian Aset',
                'route' => route('web.asset-search.index'),
                'active' => request()->routeIs('web.asset-search.*'),
                'icon' => file_get_contents(base_path('ICON/Pencarian Aset icon.svg')),
            ],
            [
                'label' => 'Damage Reports',
                'route' => route('web.damage-reports.index'),
                'active' => request()->routeIs('web.damage-reports.*'),
                'icon' => <<<'SVG'
<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M10 11.5C10 10.1193 11.1193 9 12.5 9H35.5C36.8807 9 38 10.1193 38 11.5V27.5C38 28.8807 36.8807 30 35.5 30H25.5L19 36V30H12.5C11.1193 30 10 28.8807 10 27.5V11.5Z" stroke="white" stroke-width="3.5" stroke-linejoin="round"/>
    <path d="M24 15V22" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
    <circle cx="24" cy="26" r="2.2" fill="white"/>
</svg>
SVG,
            ],
            [
                'label' => 'Ruangan & Gedung',
                'route' => route('web.locations.index'),
                'active' => request()->routeIs('web.locations.*', 'web.location-assets.*', 'web.location-maps.*'),
                'icon' => file_get_contents(base_path('ICON/Ruangan dan gedung icon.svg')),
            ],
            [
                'label' => 'Browse Asset',
                'route' => route('web.assets.index'),
                'active' => request()->routeIs('web.assets.*'),
                'icon' => file_get_contents(base_path('ICON/Browse aset icon.svg')),
            ],
        ];

        $pageHeading = trim($__env->yieldContent('page-heading', ''));
        $pageEyebrow = trim($__env->yieldContent('page-eyebrow', ''));
        $pageActions = trim($__env->yieldContent('page-actions', ''));
        $hasPageHeader = $pageHeading !== '' || $pageEyebrow !== '' || $pageActions !== '';
    @endphp
    <body class="min-h-screen bg-[#eef1f7] text-slate-950 antialiased">
        <div class="flex min-h-screen flex-col overflow-hidden bg-[#eef1f7] lg:grid lg:grid-cols-[320px_minmax(0,1fr)]">
                <aside class="flex flex-col bg-[#33457f] px-5 py-6 text-white lg:px-6 lg:py-7">
                    <a class="flex items-center gap-4 rounded-[24px] px-3 py-2" href="{{ route('dashboard') }}">
                        <span class="grid h-16 w-16 place-items-center rounded-[22px] bg-white/10 ring-1 ring-white/15">
                            <svg aria-hidden="true" class="h-11 w-11" fill="none" viewBox="0 0 64 64">
                                <path d="M32 6L49.5 13.5V29C49.5 40.5 42.4 50.9 32 56C21.6 50.9 14.5 40.5 14.5 29V13.5L32 6Z" fill="#EAF0FF" stroke="#9AB1F5" stroke-width="3"/>
                                <path d="M28 19.5C28 18.6716 28.6716 18 29.5 18H34.5C35.3284 18 36 18.6716 36 19.5V27H43.5C44.3284 27 45 27.6716 45 28.5V33.5C45 34.3284 44.3284 35 43.5 35H36V42.5C36 43.3284 35.3284 44 34.5 44H29.5C28.6716 44 28 43.3284 28 42.5V35H20.5C19.6716 35 19 34.3284 19 33.5V28.5C19 27.6716 19.6716 27 20.5 27H28V19.5Z" fill="#33457f"/>
                            </svg>
                        </span>

                        <span class="min-w-0">
                            <span class="block text-xl font-extrabold tracking-tight">RS Mitra Husada</span>
                            <span class="mt-1 block text-sm font-medium text-white/75">Sistem pelacakan aset rumah sakit</span>
                        </span>
                    </a>

                    <nav class="mt-8 flex flex-col gap-2">
                        @foreach ($navItems as $item)
                            <a
                                class="group flex items-center gap-4 rounded-[22px] px-4 py-4 transition {{ $item['active'] ? 'bg-white/22 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]' : 'hover:bg-white/10' }}"
                                href="{{ $item['route'] }}"
                            >
                                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-[18px] {{ $item['active'] ? 'bg-white/14' : 'bg-transparent' }}">
                                    <span class="block h-9 w-9 [&>svg]:h-full [&>svg]:w-full">
                                        {!! $item['icon'] !!}
                                    </span>
                                </span>
                                <span class="text-2xl font-semibold tracking-tight text-white lg:text-[1.9rem]">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="mt-auto hidden rounded-[24px] bg-white/8 p-4 text-sm text-white/80 ring-1 ring-white/10 lg:block">
                        <p class="font-semibold text-white">Panel Operasional</p>
                        <p class="mt-2 leading-6">Gunakan sidebar untuk berpindah antar modul utama tanpa keluar dari alur kerja.</p>
                    </div>
                </aside>

                <div class="min-w-0 bg-[#eef1f7]">
                    <div class="flex min-h-screen flex-col">
                        @if ($hasPageHeader)
                            <header class="p-3 lg:p-4">
                                <div class="flex flex-col gap-4 rounded-[24px] bg-white px-6 py-6 shadow-sm ring-1 ring-black/5 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                                    <div class="min-w-0">
                                        @if ($pageEyebrow !== '')
                                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">{!! html_entity_decode($pageEyebrow, ENT_QUOTES, 'UTF-8') !!}</p>
                                        @endif

                                        @if ($pageHeading !== '')
                                            <h1 class="mt-2 text-4xl font-black tracking-tight text-slate-950 lg:text-5xl">{!! html_entity_decode($pageHeading, ENT_QUOTES, 'UTF-8') !!}</h1>
                                        @endif
                                    </div>

                                    @hasSection('page-actions')
                                        <div class="flex flex-wrap items-center gap-3">
                                            @yield('page-actions')
                                        </div>
                                    @endif
                                </div>
                            </header>
                        @endif

                        <main class="flex-1 p-3 pt-0 lg:p-4 lg:pt-0">
                            <div class="space-y-4">
                                @if (session('status_message'))
                                    <div class="rounded-[20px] border px-5 py-4 text-sm font-medium shadow-sm {{ session('status_type', 'success') === 'error' ? 'border-red-200 bg-red-50 text-red-900' : 'border-emerald-200 bg-emerald-50 text-emerald-900' }}">
                                        {{ session('status_message') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="rounded-[20px] border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm">
                                        <p class="text-sm font-semibold text-amber-900">Masih ada data yang perlu diperbaiki sebelum dilanjutkan.</p>
                                        <ul class="mt-2 space-y-1 text-sm text-amber-800">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @yield('content')
                            </div>
                        </main>
                    </div>
                </div>
        </div>
    </body>
</html>
