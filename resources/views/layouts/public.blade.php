<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b6e4f">
    <title>{{ isset($title) ? $title.' · ' : '' }}LASU Safety Watch</title>
    <meta name="description" content="Campus security and incident reporting system for Lagos State University.">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
@php
    $links = [
        ['home', 'Home'], ['report.create', 'Report incident'], ['map', 'Live map'],
        ['safety', 'Safety Center'], ['track', 'Track report'],
    ];
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-[1000] border-b border-slate-200/70 bg-white/85 backdrop-blur">
    <div class="container-app flex h-16 items-center justify-between gap-4">
        <x-logo />
        <nav class="hidden items-center gap-1 lg:flex">
            @foreach ($links as [$route, $label])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) ? 'nav-link-active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="hidden items-center gap-2 lg:flex">
            @auth
                @if (auth()->user()->isStaff())
                    <a href="{{ route('staff.dashboard') }}" class="btn-light"><x-icon name="chart" class="size-4" />Dashboard</a>
                @else
                    <a href="{{ route('reports.mine') }}" class="btn-light"><x-icon name="list" class="size-4" />My reports</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-ghost" title="Log out"><x-icon name="logout" class="size-4" /></button></form>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
            @endauth
            <a href="{{ route('safety') }}#sos" class="btn-danger"><x-icon name="siren" class="size-4" />SOS</a>
        </div>
        <button class="btn-ghost lg:hidden" @click="open = !open" aria-label="Menu">
            <x-icon name="menu" x-show="!open" /><x-icon name="x" x-show="open" x-cloak />
        </button>
    </div>
    <div x-show="open" x-cloak x-transition class="border-t border-slate-200 bg-white lg:hidden">
        <nav class="container-app flex flex-col gap-1 py-3">
            @foreach ($links as [$route, $label])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) ? 'nav-link-active' : '' }}">{{ $label }}</a>
            @endforeach
            @auth
                <a href="{{ auth()->user()->isStaff() ? route('staff.dashboard') : route('reports.mine') }}" class="nav-link">{{ auth()->user()->isStaff() ? 'Dashboard' : 'My reports' }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-link w-full text-left">Log out</button></form>
            @else
                <a href="{{ route('login') }}" class="nav-link">Log in</a>
            @endauth
            <a href="{{ route('safety') }}#sos" class="btn-danger mt-2"><x-icon name="siren" class="size-4" />Emergency SOS</a>
        </nav>
    </div>
</header>

<main class="flex-1">
    @yield('content')
</main>

<footer class="mt-16 border-t border-slate-200 bg-white">
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
