@extends('layouts.staff')
@php $title = 'Overview'; @endphp

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm text-slate-500">{{ now()->format('l, j F Y') }}</p>
        <h2 class="text-2xl font-extrabold text-slate-900">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }}</h2>
    </div>
    <a href="{{ route('staff.reports.index', ['status' => 'pending']) }}" class="btn-primary"><x-icon name="list" class="size-4" />Review pending ({{ $kpis['pending'] }})</a>
</div>

{{-- KPIs --}}
<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach ([
        ['Total reports', $kpis['total'], 'list', 'bg-slate-100 text-slate-700', null],
        ['Pending review', $kpis['pending'], 'clock', 'bg-amber-100 text-amber-700', 'pending'],
        ['Active cases', $kpis['active'], 'bolt', 'bg-indigo-100 text-indigo-700', 'in_progress'],
        ['Resolved this month', $kpis['resolved_month'], 'check', 'bg-emerald-100 text-emerald-700', 'resolved'],
        ['Avg. resolution time', $kpis['avg_hours'] !== null ? $kpis['avg_hours'].' hrs' : '-', 'chart', 'bg-sky-100 text-sky-700', null],
    ] as [$label, $value, $icon, $tone, $filter])
        <a @if($filter) href="{{ route('staff.reports.index', ['status' => $filter]) }}" @endif class="card p-5 transition {{ $filter ? 'hover:shadow-md' : '' }}">
            <span class="grid size-10 place-items-center rounded-xl {{ $tone }}"><x-icon :name="$icon" /></span>
            <p class="mt-4 text-3xl font-extrabold text-slate-900">{{ $value }}</p>
            <p class="text-sm text-slate-500">{{ $label }}</p>
        </a>
    @endforeach
</div>

@if ($kpis['critical_open'])
    <div class="mt-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <x-icon name="alert" class="size-5 shrink-0" />
        <span><b>{{ $kpis['critical_open'] }} critical</b> {{ Str::plural('case', $kpis['critical_open']) }} still open.</span>
        <a href="{{ route('staff.reports.index', ['severity' => 'critical']) }}" class="ml-auto font-semibold underline">View</a>
    </div>
@endif

{{-- Charts --}}
<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="card p-5 xl:col-span-2">
        <h3 class="font-bold text-slate-900">Reports in the last 14 days</h3>
        <div class="mt-4 h-64"><canvas id="trendChart"></canvas></div>
    </div>
    <div class="card p-5">
        <h3 class="font-bold text-slate-900">By category</h3>
        <div class="mt-4 h-64"><canvas id="catChart"></canvas></div>
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="card overflow-hidden xl:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold text-slate-900">Needs attention</h3>
            <a href="{{ route('staff.reports.index') }}" class="text-sm font-semibold text-brand-700">All reports →</a>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse ($urgent as $r)
                <li>
                    <a href="{{ route('staff.reports.show', $r) }}" class="flex flex-wrap items-center gap-3 px-5 py-3.5 hover:bg-slate-50">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg text-white" style="background: {{ $r->category->color }}"><x-icon :name="$r->category->icon" class="size-4" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $r->title }}</p>
                            <p class="text-xs text-slate-500">{{ $r->reference }} · {{ $r->location_name }} · {{ $r->created_at->diffForHumans() }}</p>
                        </div>
                        <x-severity-badge :severity="$r->severity" />
                        <x-status-badge :status="$r->status" />
                    </a>
                </li>
            @empty
                <li class="px-5 py-10 text-center text-sm text-slate-500">All caught up. No open reports.</li>
            @endforelse
        </ul>
    </div>
    <div class="space-y-6">
        <div class="card p-5">
            <h3 class="font-bold text-slate-900">Hotspots</h3>
            <p class="text-xs text-slate-500">Locations with the most reports</p>
            @php $max = max(1, $hotspots->max('total')); @endphp
            <ul class="mt-4 space-y-3">
                @foreach ($hotspots as $h)
                    <li>
                        <div class="flex justify-between text-sm"><span class="font-medium text-slate-700">{{ $h->location_name }}</span><span class="font-bold text-slate-900">{{ $h->total }}</span></div>
                        <div class="mt-1 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-gradient-to-r from-brand-500 to-brand-700" style="width: {{ $h->total / $max * 100 }}%"></div></div>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card p-5">
            <h3 class="font-bold text-slate-900">By status</h3>
            <div class="mt-4 h-44"><canvas id="statusChart"></canvas></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    const trend = @json($trend);
    const cats = @json($byCategory);
    const statuses = @json($byStatus);
    lswChart(document.getElementById('trendChart'), {
        type: 'line',
        data: { labels: trend.labels, datasets: [{ data: trend.data, label: 'Reports', borderColor: '#0b6e4f', backgroundColor: 'rgba(16,185,129,.12)', fill: true, tension: .35, pointRadius: 3 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } } },
    });
    lswChart(document.getElementById('catChart'), {
        type: 'doughnut',
        data: { labels: cats.map(c => c.name), datasets: [{ data: cats.map(c => c.count), backgroundColor: cats.map(c => c.color), borderWidth: 2 }] },
        options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } } },
    });
    lswChart(document.getElementById('statusChart'), {
        type: 'bar',
        data: { labels: statuses.map(s => s.label), datasets: [{ data: statuses.map(s => s.count), backgroundColor: ['#f59e0b', '#0ea5e9', '#6366f1', '#10b981', '#94a3b8'], borderRadius: 6 }] },
        options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } }, y: { grid: { display: false } } } },
    });
</script>
@endpush
