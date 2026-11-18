<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}Staff · LASU Safety Watch</title>
    <link rel="icon" href="/favicon-64.png" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ side: false }">
@php
    $user = auth()->user();
    $nav = [
        ['staff.dashboard', 'Overview', 'chart'],
        ['staff.reports.index', 'Reports', 'list'],
    ];
    if ($user->isAdmin()) $nav[] = ['admin.users.index', 'Users & roles', 'users'];
@endphp
<div class="min-h-screen lg:pl-64">
    {{-- Sidebar --}}
    <div x-show="side" x-cloak @click="side = false" class="fixed inset-0 z-[1500] bg-slate-900/50 lg:hidden"></div>
    <aside :class="side ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-[1600] flex w-64 flex-col bg-gradient-to-b from-brand-900 to-brand-950 px-4 py-5 transition-transform">
        <x-logo :light="true" />
        <p class="mt-8 px-3 text-[11px] font-semibold uppercase tracking-wider text-brand-200/60">{{ $user->unit() ? config('lsw.units.'.$user->unit()) : 'Administration' }}</p>
        <nav class="mt-2 flex flex-col gap-1">
            @foreach ($nav as [$route, $label, $icon])
                <a href="{{ route($route) }}" class="side-link {{ request()->routeIs($route.'*') || request()->routeIs(str_replace('.index', '', $route).'.*') ? 'side-link-active' : '' }}">
                    <x-icon :name="$icon" class="size-5" />{{ $label }}
                </a>
            @endforeach
            <a href="{{ route('map') }}" class="side-link"><x-icon name="map" class="size-5" />Public map</a>
            <a href="{{ route('home') }}" class="side-link"><x-icon name="home" class="size-5" />Public site</a>
        </nav>
        <div class="mt-auto rounded-2xl bg-white/10 p-3">
            <div class="flex items-center gap-3">
                <span class="grid size-9 place-items-center rounded-full bg-brand-500 text-sm font-bold text-white">{{ $user->initials() }}</span>
                <div class="min-w-0 leading-tight">
                    <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-brand-200/80">{{ $user->roleLabel() }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf
                <button class="side-link w-full justify-center bg-white/5"><x-icon name="logout" class="size-4" />Log out</button>
            </form>
        </div>
    </aside>

    {{-- Top bar --}}
    <header class="sticky top-0 z-[1000] flex h-16 items-center gap-3 border-b border-slate-200 bg-white/85 px-4 backdrop-blur sm:px-6 lg:px-8">
        <button class="btn-ghost -ml-2 lg:hidden" @click="side = true" aria-label="Open menu"><x-icon name="menu" /></button>
        <h1 class="text-lg font-bold text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
        <form action="{{ route('staff.reports.index') }}" class="ml-auto hidden w-72 sm:block">
            <label class="relative block">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input name="q" value="{{ request('q') }}" placeholder="Search reports or LSW- code" class="input py-2 pl-9">
            </label>
        </form>
    </header>

    {{-- Live SOS alerts --}}
    <div x-data="sosAlerts('{{ route('staff.sos-feed') }}')" x-cloak>
        <template x-if="alerts.length">
            <div class="border-b border-red-700 bg-red-600 px-4 py-2.5 text-white sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <span class="flex items-center gap-2 font-bold"><span class="relative flex size-3"><span class="absolute inline-flex size-full animate-ping rounded-full bg-white opacity-75"></span><span class="relative inline-flex size-3 rounded-full bg-white"></span></span>
                        <span x-text="alerts.length + ' active SOS alert' + (alerts.length > 1 ? 's' : '')"></span></span>
                    <template x-for="a in alerts" :key="a.id">
                        <a :href="a.url" class="rounded-lg bg-white/15 px-2.5 py-1 text-sm font-semibold hover:bg-white/25" x-text="a.ref + ' · ' + a.when"></a>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <main class="px-4 py-6 sm:px-6 lg:px-8">
        <x-flash />
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
