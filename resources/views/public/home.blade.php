@extends('layouts.public')

{{-- Design rules: .claude/CLAUDE.md (civic, high-trust; no bento grids, no background patterns, red only for SOS) --}}

@section('content')
{{-- ============ HERO: asymmetric 60/40 ============ --}}
<section class="border-b border-line bg-surface">
    <div class="container-app grid items-start gap-10 py-12 sm:py-16 lg:grid-cols-[3fr_2fr] lg:gap-14 lg:py-20">

        {{-- Left: value proposition --}}
        <div class="lg:pt-6">
            <p class="flex items-center gap-2 text-sm font-semibold text-muted">
                <span class="size-2 rounded-full bg-gold"></span>
                Lagos State University · Ojo Campus
            </p>

            <h1 class="mt-5 text-[2.5rem] font-extrabold leading-[1.1] tracking-[-0.03em] text-ink sm:text-6xl">
                <span class="block">Report it.</span>
                <span class="block text-brand-500">We'll act on it.</span>
            </h1>

            <p class="mt-6 max-w-xl text-lg leading-relaxed text-muted">
                Theft, cult activity, harassment, broken facilities or flooding on campus: send it in under a minute.
                It goes straight to the LASU unit responsible, and you can stay anonymous.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
                <a href="{{ route('report.create') }}" class="btn-primary btn-lg">
                    Report an Incident <x-icon name="arrow-right" class="size-5" />
                </a>
                <a href="{{ route('track') }}" class="inline-flex min-h-12 items-center justify-center gap-1.5 font-semibold text-brand-700 underline-offset-4 hover:underline sm:justify-start">
                    Track existing report
                </a>
            </div>

            <ul class="mt-10 flex flex-col gap-3 text-sm text-muted sm:flex-row sm:flex-wrap sm:gap-x-6">
                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 text-brand-600" />No login needed</li>
                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 text-brand-600" />Anonymous option</li>
                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 text-brand-600" />Every report gets a tracking code</li>
            </ul>
        </div>

        {{-- Right: interactive quick-report selector + live campus status --}}
        <div x-data="{ sel: null }" class="card overflow-hidden">
            <div class="border-b border-line px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-bold text-ink">What do you need to report?</h2>
                    <span class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                        <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-60"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span></span>
                        Live
                    </span>
                </div>
                <p class="mt-1 font-mono text-xs text-muted">
                    {{ $stats['open'] }} open {{ Str::plural('case', $stats['open']) }}
                    @if ($stats['last_report']) · last report {{ $stats['last_report']->diffForHumans() }} @endif
                </p>
            </div>

            <ul class="divide-y divide-line">
                @foreach ($categories as $c)
                    <li>
                        <button type="button" @click="sel = sel === {{ $c->id }} ? null : {{ $c->id }}" :aria-expanded="sel === {{ $c->id }}"
                                class="flex min-h-16 w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-surface focus-visible:bg-surface focus-visible:outline-none"
                                :class="sel === {{ $c->id }} && 'bg-surface'">
                            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700"><x-icon :name="$c->icon" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-1.5 font-semibold text-ink">
                                    {{ $c->name }}
                                    @if ($c->is_confidential)<x-icon name="lock" class="size-3.5 text-muted" />@endif
                                </span>
                                <span class="block truncate text-xs text-muted">{{ $c->unitLabel() }}</span>
                            </span>
                            <span class="font-mono text-xs text-muted">{{ $openByCategory[$c->id] ?? 0 }} open</span>
                            <x-icon name="arrow-right" class="size-4 shrink-0 text-muted transition" x-bind:class="sel === {{ $c->id }} && 'rotate-90'" />
                        </button>
                        <div x-show="sel === {{ $c->id }}" x-cloak class="bg-surface px-5 pb-4">
                            <p class="mb-2 text-xs font-semibold text-muted">Choose what happened:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (($c->subtypes ?? []) as $sub)
                                    <a href="{{ route('report.create', ['type' => $c->id, 'subtype' => $sub]) }}"
                                       class="inline-flex min-h-11 items-center rounded-lg border border-line bg-white px-3 text-sm font-medium text-ink hover:border-brand-600 hover:text-brand-700">{{ $sub }}</a>
                                @endforeach
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <p class="flex items-start gap-2 border-t border-line px-5 py-3 text-xs text-muted">
                <x-icon name="lock" class="mt-0.5 size-3.5 shrink-0" />
                Harassment & GBV reports are always anonymous and never shown publicly.
            </p>
        </div>
    </div>
</section>

{{-- ============ METRICS: inline typographic strip ============ --}}
<section class="border-b border-line bg-white" aria-label="Campus statistics">
    <dl class="container-app grid grid-cols-2 gap-y-6 py-6 sm:flex sm:flex-wrap sm:items-baseline sm:gap-x-10">
        @foreach ([
            [$stats['total'], 'reports received'],
            [$stats['resolved'], 'resolved'],
            [$stats['this_week'], 'this week'],
            [$stats['avg_hours'] !== null ? $stats['avg_hours'].'h' : '-', 'average resolution time'],
        ] as [$value, $label])
            <div class="flex items-baseline gap-2">
                <dt class="sr-only">{{ $label }}</dt>
                <dd class="font-mono text-2xl font-semibold tracking-tight text-ink">{{ $value }}</dd>
                <span class="text-sm text-muted">{{ $label }}</span>
            </div>
        @endforeach
    </dl>
</section>

{{-- ============ HOW IT WORKS ============ --}}
<section class="container-app py-16">
    <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-ink sm:text-3xl">From report to resolution</h2>
    <ol class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Report', 'Describe what happened, pin the location, add a photo. Login is optional.'],
            ['Get a code', 'You receive a tracking code such as LSW-7K3P9Q to follow progress.'],
            ['Verified & assigned', 'The responsible unit checks the report and assigns an officer.'],
            ['Resolved', 'Every update is visible on your tracking page until the case is closed.'],
        ] as $i => [$h, $p])
            <li class="border-t-2 border-brand-700 pt-4">
                <span class="font-mono text-sm font-semibold text-brand-700">{{ sprintf('%02d', $i + 1) }}</span>
                <h3 class="mt-1 text-lg font-bold text-ink">{{ $h }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-muted">{{ $p }}</p>
            </li>
        @endforeach
    </ol>
</section>

{{-- ============ RECENT VERIFIED REPORTS ============ --}}
<section class="container-app pb-4">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-ink sm:text-3xl">Recently verified</h2>
            <p class="mt-1 text-sm text-muted">Reports confirmed by LASU responders. Confidential cases are excluded.</p>
        </div>
        <a href="{{ route('map') }}" class="inline-flex min-h-12 items-center gap-1.5 font-semibold text-brand-700 underline-offset-4 hover:underline">
            Open live map <x-icon name="arrow-right" class="size-4" />
        </a>
    </div>

    <div class="card mt-6 divide-y divide-line">
        @forelse ($recent as $r)
            <a href="{{ route('track', ['ref' => $r->reference]) }}" class="flex flex-col gap-2 px-5 py-4 transition hover:bg-surface sm:flex-row sm:items-center sm:gap-6">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $r->category->name }}</p>
                    <p class="mt-0.5 font-semibold text-ink">{{ $r->title }}</p>
                    <p class="mt-0.5 text-sm text-muted">{{ $r->location_name }} · {{ $r->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs text-muted">{{ $r->reference }}</span>
                    <x-status-badge :status="$r->status" />
                </div>
            </a>
        @empty
            <p class="px-5 py-10 text-center text-muted">No verified reports yet.</p>
        @endforelse
    </div>
</section>
@endsection
