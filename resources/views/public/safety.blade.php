@extends('layouts.public')
@php $title = 'Safety Center'; @endphp

{{-- Civic layout: white surfaces, navy text, red reserved for the SOS trigger itself. --}}
@section('content')
@php
    $contacts = collect(config('lsw.contacts'));
    $urgent = $contacts->where('level', 'critical');
    $support = $contacts->where('level', '!=', 'critical');
@endphp

<section class="border-b border-line bg-white">
    <div class="container-app flex flex-col gap-1 py-5 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
        <h1 class="text-2xl font-extrabold leading-tight tracking-[-0.03em] text-ink sm:text-3xl">Get help fast</h1>
        <p class="text-sm text-muted sm:text-base">
            In danger now? Press <b class="text-ink">SOS</b>. Otherwise call a unit below or
            <a href="{{ route('report.create') }}" class="font-semibold text-brand-500 underline-offset-4 hover:underline">file a report</a>.
        </p>
    </div>
</section>

<div class="container-app grid gap-6 py-8 lg:grid-cols-[3fr_2fr] lg:gap-8">

    {{-- ================= SOS ================= --}}
    <section id="sos" class="card flex scroll-mt-24 flex-col p-6 sm:p-8" x-data="sosButton('{{ route('sos') }}')" aria-labelledby="sos-title">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="sos-title" class="text-xl font-bold text-ink">Emergency SOS</h2>
                <p class="mt-1 text-muted">Sends your location to LASU Campus Security. No login needed.</p>
            </div>
            <span class="hidden shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 sm:inline-flex">
                <span class="size-1.5 rounded-full bg-emerald-500"></span> Monitored 24/7
            </span>
        </div>

        <div class="mt-8 grid items-center gap-8 sm:grid-cols-[auto_1fr]">
            {{-- The single red element on this page --}}
            <div class="flex justify-center">
                <button type="button" x-show="stage === 'idle'" @click="start()" data-sos-trigger
                        class="sos-pulse grid size-40 place-items-center rounded-full bg-sos text-white shadow-lg ring-8 ring-red-50 transition hover:scale-[1.03] focus:outline-none focus-visible:ring-red-200">
                    <span class="text-center">
                        <span class="block text-4xl font-black tracking-wider">SOS</span>
                        <span class="block text-xs font-semibold opacity-90">Tap for help</span>
                    </span>
                </button>
                <div x-show="stage === 'countdown'" x-cloak class="grid size-40 place-items-center rounded-full border-4 border-ink bg-white">
                    <span class="text-center">
                        <span class="block font-mono text-6xl font-semibold text-ink" x-text="count"></span>
                        <span class="block text-xs font-semibold text-muted">seconds</span>
                    </span>
                </div>
                <div x-show="stage === 'sending'" x-cloak class="grid size-40 place-items-center rounded-full border-4 border-line bg-white text-sm font-semibold text-muted">Sending…</div>
                <div x-show="stage === 'sent'" x-cloak class="grid size-40 place-items-center rounded-full bg-emerald-50 text-emerald-700"><x-icon name="check" class="size-16" /></div>
                <div x-show="stage === 'error'" x-cloak class="grid size-40 place-items-center rounded-full bg-amber-50 text-amber-700"><x-icon name="alert" class="size-16" /></div>
            </div>

            <div aria-live="polite">
                {{-- idle --}}
                <div x-show="stage === 'idle'">
                    <ol class="space-y-3 text-sm">
                        <li class="flex gap-3"><span class="font-mono font-semibold text-brand-500">01</span><span class="text-ink">Tap <b>SOS</b>. A 5-second countdown starts, and you can cancel it.</span></li>
                        <li class="flex gap-3"><span class="font-mono font-semibold text-brand-500">02</span><span class="text-ink">Your phone shares your GPS location (allow it if asked).</span></li>
                        <li class="flex gap-3"><span class="font-mono font-semibold text-brand-500">03</span><span class="text-ink">Campus Security gets an instant alert and a reference code.</span></li>
                    </ol>
                    <label for="note" class="mt-5 block text-sm font-semibold text-ink">What is happening? <span class="font-normal text-muted">(optional)</span></label>
                    <textarea id="note" x-model="note" rows="2" maxlength="300" class="input mt-1.5" placeholder="e.g. Fight at Hostel B gate"></textarea>
                </div>
                {{-- countdown --}}
                <div x-show="stage === 'countdown'" x-cloak>
                    <p class="text-lg font-bold text-ink">Sending alert in <span class="font-mono" x-text="count"></span>s</p>
                    <p class="mt-1 text-sm text-muted">Pressed by mistake? Cancel now.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <button type="button" @click="cancel()" class="btn-light btn-lg">Cancel</button>
                        <button type="button" @click="send()" class="btn-primary btn-lg">Send now</button>
                    </div>
                </div>
                {{-- sending --}}
                <p x-show="stage === 'sending'" x-cloak class="text-lg font-semibold text-ink">Getting your location and alerting Campus Security…</p>
                {{-- sent --}}
                <div x-show="stage === 'sent'" x-cloak>
                    <p class="text-lg font-bold text-ink">Alert sent to Campus Security</p>
                    <p class="mt-1 text-sm text-muted" x-text="exact ? 'Your GPS location was shared. Stay where you are if it is safe.' : 'Your exact location could not be read. Please also call Campus Security.'"></p>
                    <p class="mt-3 text-sm text-muted">Reference <span class="font-mono font-semibold text-ink" x-text="reference"></span></p>
                    <button type="button" @click="stage = 'idle'" class="btn-light mt-4">Done</button>
                </div>
                {{-- error --}}
                <div x-show="stage === 'error'" x-cloak>
                    <p class="text-lg font-bold text-ink">The alert could not be sent</p>
                    <p class="mt-1 text-sm text-muted" x-text="reference + ' Call Campus Security using the numbers on this page.'"></p>
                    <button type="button" @click="stage = 'idle'" class="btn-light mt-4">Try again</button>
                </div>
            </div>
        </div>

        @php $security = $urgent->first(); @endphp
        <div class="mt-auto pt-8">
            <a href="tel:{{ $security['phone'] }}" class="flex min-h-14 items-center justify-between gap-4 rounded-xl border border-line bg-surface px-4 py-3 transition hover:border-brand-500">
                <span>
                    <span class="block text-sm font-semibold text-ink">Can't use SOS? Call Campus Security</span>
                    <span class="block text-xs text-muted">No data or GPS needed</span>
                </span>
                <span class="inline-flex items-center gap-2 font-mono text-sm font-semibold text-brand-700"><x-icon name="phone" class="size-4" />{{ $security['phone'] }}</span>
            </a>
        </div>
    </section>

    {{-- ================= CONTACTS ================= --}}
    <section class="card overflow-hidden" aria-labelledby="contacts-title">
        <div class="border-b border-line px-6 py-5">
            <h2 id="contacts-title" class="text-xl font-bold text-ink">Who to call</h2>
            <p class="mt-1 text-sm text-muted">Tap a number to call from your phone.</p>
        </div>
        @foreach ([['Emergency lines', $urgent], ['Campus support', $support]] as [$group, $list])
            <p class="bg-surface px-6 py-2 text-xs font-semibold uppercase tracking-wide text-muted">{{ $group }}</p>
            <ul class="divide-y divide-line">
                @foreach ($list as $c)
                    <li>
                        <a href="tel:{{ $c['phone'] }}" class="flex min-h-16 items-center gap-4 px-6 py-3 transition hover:bg-surface focus-visible:bg-surface focus-visible:outline-none">
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold leading-snug text-ink">{{ $c['name'] }}</span>
                                <span class="block text-sm text-muted">{{ $c['desc'] }}</span>
                            </span>
                            <span class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-xl border border-line bg-white px-3 font-mono text-sm font-semibold text-brand-700">
                                <x-icon name="phone" class="size-4" />{{ $c['phone'] }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </section>
</div>

{{-- ================= SOS OR REPORT? ================= --}}
<section class="container-app pb-4">
    <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-ink">SOS or report?</h2>
    <div class="mt-5 grid gap-4 md:grid-cols-2">
        <div class="card p-6">
            <h3 class="font-bold text-ink">Use SOS when</h3>
            <ul class="mt-3 space-y-2 text-sm text-ink">
                @foreach (['You or someone near you is in danger right now', 'There is a fight, attack or cult threat happening', 'Someone is injured and needs help urgently'] as $item)
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-ink" />{{ $item }}</li>
                @endforeach
            </ul>
        </div>
        <div class="card p-6">
            <h3 class="font-bold text-ink">Use a report when</h3>
            <ul class="mt-3 space-y-2 text-sm text-ink">
                @foreach (['It already happened and you are now safe', 'Something is broken: lights, water, roofs, drains', 'You want to report harassment confidentially'] as $item)
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-brand-500" />{{ $item }}</li>
                @endforeach
            </ul>
            <a href="{{ route('report.create') }}" class="btn-primary btn-lg mt-5 w-full sm:w-auto">Report an Incident <x-icon name="arrow-right" class="size-5" /></a>
        </div>
    </div>
</section>

{{-- ================= TIPS ================= --}}
<section class="container-app py-12">
    <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-ink">Stay safe on campus</h2>
    <div class="mt-5 grid gap-x-10 gap-y-8 md:grid-cols-2">
        @foreach (config('lsw.tips') as $heading => $tips)
            <div class="border-t border-line pt-4">
                <h3 class="font-bold text-ink">{{ $heading }}</h3>
                <ul class="mt-3 space-y-2">
                    @foreach ($tips as $tip)
                        <li class="flex gap-2 text-sm leading-relaxed text-muted"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-gold"></span>{{ $tip }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</section>
@endsection
