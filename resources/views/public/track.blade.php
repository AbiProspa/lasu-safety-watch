@extends('layouts.public')
@php $title = 'Track a report'; @endphp

@section('content')
<div class="container-app max-w-3xl py-10">
    <p class="text-sm font-bold uppercase tracking-wider text-brand-700">Track</p>
    <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Follow your report</h1>
    <p class="mt-2 text-slate-500">Enter the tracking code you received after submitting (e.g. LSW-7K3P9Q).</p>

    <form class="mt-6 flex gap-3" method="GET">
        <input name="ref" value="{{ $ref }}" required class="input font-mono text-base uppercase tracking-widest" placeholder="LSW-XXXXXX">
        <button class="btn-primary px-6"><x-icon name="search" class="size-4" />Track</button>
    </form>

    @if ($ref && ! $report)
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">No report found with code <b>{{ $ref }}</b>. Check the code and try again.</div>
    @endif

    @if ($report)
        <div class="card mt-8 overflow-hidden">
            <div class="border-b border-slate-100 p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-sm font-bold text-slate-500">{{ $report->reference }}</span>
                    <x-status-badge :status="$report->status" />
                    <x-severity-badge :severity="$report->severity" />
                </div>
                <h2 class="mt-2 text-xl font-bold text-slate-900">{{ $report->category->is_confidential ? 'Confidential report' : $report->title }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $report->category->name }} · {{ $report->location_name }} · submitted {{ $report->created_at->format('d M Y, g:ia') }}</p>
            </div>

            {{-- progress --}}
            @php
                $steps = ['pending' => 'Received', 'verified' => 'Verified', 'in_progress' => 'In progress', 'resolved' => 'Resolved'];
                $order = array_keys($steps);
                $current = $report->status === 'rejected' ? -1 : array_search($report->status, $order);
            @endphp
            <div class="bg-slate-50 px-6 py-5">
                @if ($report->status === 'rejected')
                    <p class="text-sm font-semibold text-slate-600">This report was closed without action (e.g. duplicate or could not be verified).</p>
                @else
                    <ol class="grid grid-cols-4 gap-2">
                        @foreach ($steps as $key => $label)
                            @php $done = $loop->index <= $current; @endphp
                            <li>
                                <div class="h-2 rounded-full {{ $done ? 'bg-brand-600' : 'bg-slate-200' }}"></div>
                                <p class="mt-2 text-xs font-semibold {{ $done ? 'text-brand-800' : 'text-slate-400' }}">{{ $label }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            <div class="p-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Updates</h3>
                <ol class="relative mt-4 space-y-5 border-l-2 border-slate-100 pl-6">
                    @foreach ($report->updates as $u)
                        <li class="relative">
                            <span class="absolute -left-[31px] top-1 size-3.5 rounded-full border-2 border-white bg-brand-600 ring-2 ring-brand-100"></span>
                            <p class="text-sm font-medium text-slate-800">{{ $u->note }}</p>
                            <p class="text-xs text-slate-400">{{ $u->created_at->format('d M Y, g:ia') }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    @endif
</div>
@endsection
