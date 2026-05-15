<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Hospital Asset Manager')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-50 text-gray-950">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
                <div>
                    <a class="text-lg font-semibold text-gray-950" href="{{ route('dashboard') }}">Hospital Asset Manager</a>
                    <p class="text-sm text-gray-600">Blade-first inventory workflows for your asset team.</p>
                </div>

                <nav class="flex items-center gap-2 text-sm">
                    <a
                        class="rounded-md px-3 py-2 font-medium transition {{ request()->routeIs('dashboard') ? 'bg-emerald-100 text-emerald-900' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-950' }}"
                        href="{{ route('dashboard') }}"
                    >
                        Dashboard
                    </a>
                    <a
                        class="rounded-md px-3 py-2 font-medium transition {{ request()->routeIs('web.assets.*') ? 'bg-sky-100 text-sky-900' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-950' }}"
                        href="{{ route('web.assets.index') }}"
                    >
                        Assets
                    </a>
                    <a
                        class="rounded-md px-3 py-2 font-medium transition {{ request()->routeIs('web.location-assets.*') ? 'bg-amber-100 text-amber-900' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-950' }}"
                        href="{{ route('web.location-assets.index') }}"
                    >
                        Locations
                    </a>
                    <a
                        class="rounded-md bg-gray-950 px-3 py-2 font-medium text-white transition hover:bg-gray-800"
                        href="{{ route('web.assets.create') }}"
                    >
                        Add Asset
                    </a>
                </nav>
            </div>
        </header>

        @if (session('status_message'))
            <div class="border-b {{ session('status_type', 'success') === 'error' ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50' }}">
                <div class="mx-auto max-w-7xl px-4 py-3 text-sm font-medium {{ session('status_type', 'success') === 'error' ? 'text-red-900' : 'text-emerald-900' }} sm:px-6 lg:px-8">
                    {{ session('status_message') }}
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="border-b border-amber-200 bg-amber-50">
                <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
                    <p class="text-sm font-semibold text-amber-900">Please fix the highlighted form errors and try again.</p>
                    <ul class="mt-2 space-y-1 text-sm text-amber-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </body>
</html>
