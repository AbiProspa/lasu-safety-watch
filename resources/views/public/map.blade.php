@extends('layouts.public')
@php $title = 'Live incident map'; @endphp

@section('content')
<div x-data="incidentMap({{ Js::from(config('lsw.campus_center')) }}, '{{ route('map.data') }}')" class="container-app py-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-brand-700">Live map</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Campus incident map</h1>
            <p class="mt-1 text-sm text-slate-500">Verified reports only. Confidential reports (harassment & GBV) are never shown.</p>
        </div>
        <select x-model.number="days" class="input w-auto">
            <option value="0">All time</option>
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
        </select>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[280px_1fr]">
        <aside class="card h-fit p-4">
            <p class="px-1 text-xs font-bold uppercase tracking-wider text-slate-400">Filter by type</p>
            <div class="mt-3 space-y-1">
                @foreach ($categories as $c)
                    <button type="button" @click="toggle({{ $c->id }})"
                            class="flex w-full items-center gap-3 rounded-xl px-2 py-2 text-left text-sm transition hover:bg-slate-50"
                            :class="hidden.includes({{ $c->id }}) && 'opacity-40'">
                        <span class="size-3 rounded-full ring-4 ring-white" style="background: {{ $c->color }}; box-shadow: 0 0 0 1px {{ $c->color }}"></span>
                        <span class="flex-1 font-medium text-slate-700">{{ $c->name }}</span>
                        <span class="text-xs font-bold text-slate-400" x-text="count({{ $c->id }})"></span>
                    </button>
                @endforeach
            </div>
            <div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">
                <span x-text="visible().length"></span> reports shown. Larger dots are critical incidents.
            </div>
            <a href="{{ route('report.create') }}" class="btn-primary mt-4 w-full"><x-icon name="alert" class="size-4" />Report an incident</a>
        </aside>
        <div class="card relative overflow-hidden">
            <div x-show="loading" class="absolute inset-0 z-[500] grid place-items-center bg-white/60 text-sm font-semibold text-slate-500">Loading map…</div>
            <div x-ref="map" class="h-[65vh] min-h-[420px] w-full"></div>
        </div>
    </div>
</div>
@endsection
