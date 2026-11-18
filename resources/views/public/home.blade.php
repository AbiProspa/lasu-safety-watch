@extends('layouts.public')

@section('content')
{{-- HERO --}}
<section class="relative overflow-hidden bg-brand-950">
    <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 20%, #10b981 0, transparent 40%), radial-gradient(circle at 85% 70%, #0b8a5f 0, transparent 45%);"></div>
    <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 40px 40px;"></div>
    <div class="container-app relative grid items-center gap-12 py-16 lg:grid-cols-2 lg:py-24">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-brand-100">
                <span class="size-2 rounded-full bg-brand-500"></span> Lagos State University · Ojo Campus
            </span>
            <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl">
                A safer campus starts with <span class="bg-gradient-to-r from-brand-200 to-emerald-300 bg-clip-text text-transparent">one report.</span>
            </h1>
            <p class="mt-5 max-w-xl text-lg text-brand-100/80">
                Report theft, cult activity, harassment, broken facilities or flooding in under a minute. It goes straight to the right LASU unit, and you can stay anonymous.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('report.create') }}" class="btn bg-white px-5 py-3 text-brand-800 shadow-lg hover:bg-brand-50">
                    <x-icon name="alert" class="size-5" /> Report an incident
                </a>
                <a href="{{ route('safety') }}#sos" class="btn bg-red-600 px-5 py-3 text-white shadow-lg hover:bg-red-700">
                    <x-icon name="siren" class="size-5" /> Emergency SOS
                </a>
                <a href="{{ route('map') }}" class="btn border border-white/20 px-5 py-3 text-white hover:bg-white/10">
                    <x-icon name="map" class="size-5" /> View live map
                </a>
            </div>
            <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-brand-100/70">
                <span class="flex items-center gap-1.5"><x-icon name="eye-off" class="size-4" /> Anonymous reporting</span>
                <span class="flex items-center gap-1.5"><x-icon name="bolt" class="size-4" /> Goes to the right unit instantly</span>
                <span class="flex items-center gap-1.5"><x-icon name="search" class="size-4" /> Track with a code</span>
            </div>
        </div>

        {{-- Stats card --}}
        <div class="grid grid-cols-2 gap-4">
            @foreach ([
                ['Reports received', $stats['total'], 'list'],
                ['Resolved', $stats['resolved'], 'check'],
                ['This week', $stats['this_week'], 'clock'],
                ['Avg. resolution', $stats['avg_hours'] !== null ? $stats['avg_hours'].'h' : '-', 'bolt'],
            ] as [$label, $value, $icon])
                <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur">
                    <span class="grid size-10 place-items-center rounded-xl bg-white/10 text-brand-200"><x-icon :name="$icon" /></span>
                    <p class="mt-4 text-3xl font-extrabold text-white">{{ $value }}</p>
                    <p class="text-sm text-brand-100/70">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- WHAT CAN I REPORT --}}
<section class="container-app py-16">
    <div class="max-w-2xl">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-700">What can I report?</p>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Every report goes to the unit that can fix it</h2>
    </div>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $cat)
            <a href="{{ route('report.create', ['type' => $cat->id]) }}" class="card group p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start gap-4">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl text-white" style="background: {{ $cat->color }}">
                        <x-icon :name="$cat->icon" class="size-6" />
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-900">{{ $cat->name }}
                            @if ($cat->is_confidential)<span class="badge ml-1 bg-purple-50 text-purple-700"><x-icon name="lock" class="size-3" />Confidential</span>@endif
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $cat->description }}</p>
                        <p class="mt-3 text-xs font-semibold text-slate-400">Handled by {{ $cat->unitLabel() }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- HOW IT WORKS --}}
<section class="border-y border-slate-200 bg-white">
    <div class="container-app py-16">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-700">How it works</p>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">From report to resolution</h2>
        <div class="mt-10 grid gap-8 md:grid-cols-4">
            @foreach ([
                ['1', 'Report', 'Describe what happened, pin the location and add a photo. Login is optional.'],
                ['2', 'Get a code', 'You receive a tracking code like LSW-7K3P9Q to follow progress.'],
                ['3', 'Verified & assigned', 'The responsible unit verifies the report and assigns an officer.'],
                ['4', 'Resolved', 'You can see every update until the issue is closed.'],
            ] as [$n, $h, $p])
                <div class="relative">
                    <span class="grid size-11 place-items-center rounded-full bg-brand-700 text-lg font-extrabold text-white ring-8 ring-brand-50">{{ $n }}</span>
                    <h3 class="mt-4 font-bold text-slate-900">{{ $h }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $p }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- RECENT --}}
<section class="container-app py-16">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-700">Campus activity</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Recently verified reports</h2>
        </div>
        <a href="{{ route('map') }}" class="btn-light">Open live map <x-icon name="arrow-right" class="size-4" /></a>
    </div>
    <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($recent as $r)
            <a href="{{ route('track', ['ref' => $r->reference]) }}" class="card flex flex-col p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold uppercase tracking-wide" style="color: {{ $r->category->color }}">{{ $r->category->name }}</span>
                    <x-status-badge :status="$r->status" />
                </div>
                <h3 class="mt-2 font-bold text-slate-900">{{ $r->title }}</h3>
                <p class="mt-1 flex-1 text-sm text-slate-500">{{ $r->shortDescription(100) }}</p>
                <div class="mt-4 flex items-center gap-3 text-xs text-slate-400">
                    <span class="flex items-center gap-1"><x-icon name="pin" class="size-3.5" />{{ $r->location_name }}</span>
                    <span class="flex items-center gap-1"><x-icon name="clock" class="size-3.5" />{{ $r->created_at->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <p class="text-slate-500">No verified reports yet.</p>
        @endforelse
    </div>
</section>
@endsection
