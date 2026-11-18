@extends('layouts.public')
@php $title = 'Report submitted'; @endphp

@section('content')
<div class="container-app max-w-xl py-16">
    <div class="card p-8 text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-100 text-emerald-600"><x-icon name="check" class="size-8" /></span>
        <h1 class="mt-5 text-2xl font-extrabold text-slate-900">Thank you. Your report has been received.</h1>
        <p class="mt-2 text-slate-500">It has been sent to <b>{{ $report->category->unitLabel() }}</b> for review.</p>

        <div class="mt-6 rounded-2xl border-2 border-dashed border-brand-200 bg-brand-50 p-5" x-data="{ copied: false }">
            <p class="text-xs font-bold uppercase tracking-wider text-brand-700">Your tracking code</p>
            <p class="mt-1 font-mono text-3xl font-extrabold tracking-widest text-brand-900">{{ $report->reference }}</p>
            <button type="button" class="btn-light mt-3 text-xs" @click="navigator.clipboard.writeText('{{ $report->reference }}'); copied = true">
                <span x-text="copied ? 'Copied!' : 'Copy code'"></span>
            </button>
            <p class="mt-3 text-xs text-slate-500">Save this code. It is the only way to follow an anonymous report.</p>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a href="{{ route('track', ['ref' => $report->reference]) }}" class="btn-primary">Track this report</a>
            <a href="{{ route('home') }}" class="btn-light">Back to home</a>
        </div>
    </div>
</div>
@endsection
