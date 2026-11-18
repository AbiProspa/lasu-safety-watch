<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B3B24">
    <title>{{ isset($title) ? $title.' · ' : '' }}LASU Safety Watch</title>
    <meta name="description" content="Campus security and incident reporting system for Lagos State University.">
    <link rel="icon" href="/favicon-64.png" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
@php
    // CLAUDE.md: max 4 centre links; the logo links home. One SOS button, far right.
    $links = [
        ['report.create', 'Report incident'], ['map', 'Live map'],
        ['safety', 'Safety Center'], ['track', 'Track report'],
    ];
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-[1000] border-b border-line bg-white">
    <div class="container-app flex h-16 items-center justify-between gap-3">
        <x-logo />
        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
            @foreach ($links as [$route, $label])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) ? 'nav-link-active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="flex items-center gap-1 sm:gap-2">
            <div class="hidden items-center gap-1 lg:flex">
                @auth
                    <a href="{{ auth()->user()->isStaff() ? route('staff.dashboard') : route('reports.mine') }}" class="nav-link">{{ auth()->user()->isStaff() ? 'Dashboard' : 'My reports' }}</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-link" title="Log out">Log out</button></form>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Log in</a>
                @endauth
            </div>
            <a href="{{ route('safety') }}#sos" class="btn-danger min-h-11 px-3 sm:px-4" data-sos-trigger>
                <x-icon name="siren" class="size-4" /><span class="sm:hidden">SOS</span><span class="hidden sm:inline">Emergency SOS</span>
            </a>
            <button class="btn-ghost min-h-11 lg:hidden" @click="open = !open" :aria-expanded="open" aria-label="Menu">
                <x-icon name="menu" x-show="!open" /><x-icon name="x" x-show="open" x-cloak />
            </button>
        </div>
    </div>
    <div x-show="open" x-cloak x-transition class="border-t border-line bg-white lg:hidden">
        <nav class="container-app flex flex-col py-2" aria-label="Mobile">
            @foreach ($links as [$route, $label])
                <a href="{{ route($route) }}" class="flex min-h-12 items-center rounded-lg px-3 text-base font-medium {{ request()->routeIs($route) ? 'bg-brand-50 text-brand-700' : 'text-ink' }}">{{ $label }}</a>
            @endforeach
            <div class="my-2 border-t border-line"></div>
            @auth
                <a href="{{ auth()->user()->isStaff() ? route('staff.dashboard') : route('reports.mine') }}" class="flex min-h-12 items-center px-3 text-base font-medium text-ink">{{ auth()->user()->isStaff() ? 'Dashboard' : 'My reports' }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex min-h-12 w-full items-center px-3 text-base font-medium text-muted">Log out</button></form>
            @else
                <a href="{{ route('login') }}" class="flex min-h-12 items-center px-3 text-base font-medium text-ink">Log in</a>
            @endauth
        </nav>
    </div>
</header>

<main class="flex-1">
    @yield('content')
</main>

<footer class="mt-16 border-t border-line bg-white">
    <div class="container-app grid gap-8 py-10 md:grid-cols-3">
        <div>
            <x-logo />
            <p class="mt-3 max-w-xs text-sm text-slate-500">{{ config('lsw.tagline') }}</p>
        </div>
        <div class="text-sm">
            <p class="font-semibold text-slate-900">Quick links</p>
            <ul class="mt-3 space-y-2 text-slate-500">
                <li><a class="hover:text-brand-700" href="{{ route('report.create') }}">Report an incident</a></li>
                <li><a class="hover:text-brand-700" href="{{ route('map') }}">Live incident map</a></li>
                <li><a class="hover:text-brand-700" href="{{ route('track') }}">Track a report</a></li>
                <li><a class="hover:text-brand-700" href="{{ route('login') }}">Staff login</a></li>
            </ul>
        </div>
        <div class="text-sm">
            <p class="font-semibold text-slate-900">In an emergency</p>
            <ul class="mt-3 space-y-2 text-slate-500">
                @foreach (array_slice(config('lsw.contacts'), 0, 3) as $c)
                    <li><a class="hover:text-brand-700" href="tel:{{ $c['phone'] }}">{{ $c['name'] }}: <b class="text-slate-700">{{ $c['phone'] }}</b></a></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="border-t border-slate-100 py-4 text-center text-xs text-slate-400">© {{ date('Y') }} LASU Safety Watch · Final year project, Lagos State University</div>
</footer>
@stack('scripts')
</body>
</html>
