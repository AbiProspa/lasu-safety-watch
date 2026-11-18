@extends('layouts.staff')
@php $title = 'Reports'; @endphp

@section('content')
<form method="GET" class="card grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-6">
    <input name="q" value="{{ request('q') }}" placeholder="Search title, code, place…" class="input lg:col-span-2">
    <select name="status" class="input">
        <option value="">All statuses</option>
        @foreach (config('lsw.statuses') as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
    </select>
    <select name="category" class="input">
        <option value="">All categories</option>
        @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
    </select>
    <select name="severity" class="input">
        <option value="">Any severity</option>
        @foreach (config('lsw.severities') as $k => $v)<option value="{{ $k }}" @selected(request('severity') === $k)>{{ $v }}</option>@endforeach
    </select>
    <div class="flex gap-2">
        <button class="btn-primary flex-1">Filter</button>
        @if (request()->hasAny(['q', 'status', 'category', 'severity', 'from', 'to']))
            <a href="{{ route('staff.reports.index') }}" class="btn-light" title="Clear"><x-icon name="x" class="size-4" /></a>
        @endif
    </div>
    <div class="flex items-center gap-2 text-sm text-slate-500 lg:col-span-3">
        <span>From</span><input type="date" name="from" value="{{ request('from') }}" class="input py-2">
        <span>to</span><input type="date" name="to" value="{{ request('to') }}" class="input py-2">
    </div>
    <div class="flex items-center justify-end lg:col-span-3">
        <a href="{{ route('staff.reports.export', request()->query()) }}" class="btn-light"><x-icon name="download" class="size-4" />Export CSV</a>
    </div>
</form>

<div class="card mt-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Report</th>
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">Location</th>
                    <th class="px-5 py-3">Severity</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Assigned</th>
                    <th class="px-5 py-3">Reported</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($reports as $r)
                    <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('staff.reports.show', $r) }}'">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-slate-900">@if($r->is_sos)<span class="badge mr-1 bg-red-600 text-white">SOS</span>@endif{{ Str::limit($r->title, 48) }}</p>
                            <p class="font-mono text-xs text-slate-400">{{ $r->reference }}</p>
                        </td>
                        <td class="px-5 py-3.5"><span class="inline-flex items-center gap-2"><span class="size-2.5 rounded-full" style="background: {{ $r->category->color }}"></span>{{ $r->category->name }}</span></td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $r->location_name }}</td>
                        <td class="px-5 py-3.5"><x-severity-badge :severity="$r->severity" /></td>
                        <td class="px-5 py-3.5"><x-status-badge :status="$r->status" /></td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $r->assignee?->name ?? '-' }}</td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-slate-500">{{ $r->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">No reports match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-3">{{ $reports->links() }}</div>
</div>
@endsection
