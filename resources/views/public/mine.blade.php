@extends('layouts.public')
@php $title = 'My reports'; @endphp

@section('content')
<div class="container-app max-w-4xl py-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">My reports</h1>
            <p class="mt-1 text-sm text-slate-500">Reports you submitted while logged in. Anonymous reports are not linked to your account.</p>
        </div>
        <a href="{{ route('report.create') }}" class="btn-primary"><x-icon name="alert" class="size-4" />New report</a>
    </div>
    <x-flash />
    <div class="mt-6 space-y-3">
        @forelse ($reports as $r)
            <a href="{{ route('track', ['ref' => $r->reference]) }}" class="card flex flex-wrap items-center gap-4 p-4 transition hover:shadow-md">
                <span class="size-2.5 rounded-full" style="background: {{ $r->category->color }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-900">{{ $r->title }}</p>
                    <p class="text-xs text-slate-500">{{ $r->reference }} · {{ $r->category->name }} · {{ $r->created_at->diffForHumans() }}</p>
                </div>
                <x-status-badge :status="$r->status" />
            </a>
        @empty
            <div class="card p-10 text-center text-slate-500">You have not submitted any reports yet.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $reports->links() }}</div>
</div>
@endsection
