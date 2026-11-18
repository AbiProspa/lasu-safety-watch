@props(['severity'])
@php
$styles = [
    'low' => 'bg-slate-100 text-slate-600',
    'medium' => 'bg-yellow-100 text-yellow-800',
    'high' => 'bg-orange-100 text-orange-700',
    'critical' => 'bg-red-600 text-white',
];
@endphp
<span {{ $attributes->merge(['class' => 'badge '.($styles[$severity] ?? $styles['low'])]) }}>{{ config("lsw.severities.$severity", $severity) }}</span>
