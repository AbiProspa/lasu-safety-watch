@props(['status'])
@php
$styles = [
    'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'verified' => 'bg-sky-50 text-sky-700 ring-sky-200',
    'in_progress' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
    'resolved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'rejected' => 'bg-slate-100 text-slate-600 ring-slate-200',
];
@endphp
<span {{ $attributes->merge(['class' => 'badge ring-1 ring-inset '.($styles[$status] ?? $styles['rejected'])]) }}>
    <span class="size-1.5 rounded-full bg-current"></span>{{ config("lsw.statuses.$status", $status) }}
</span>
