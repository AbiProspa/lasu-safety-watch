@props(['name', 'class' => 'size-5'])
@php
$paths = [
    'shield' => '<path d="M12 3l7 3v5c0 4.5-3 8.3-7 10-4-1.7-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
    'heart' => '<path d="M12 20s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 10c0 5.6-7 10-7 10z"/>',
    'home' => '<path d="M4 11l8-7 8 7v8a1 1 0 01-1 1h-4v-6h-6v6H5a1 1 0 01-1-1z"/>',
    'wrench' => '<path d="M14.7 6.3a4 4 0 00-5.4 5.1L4 16.7V20h3.3l5.3-5.3a4 4 0 005.1-5.4l-2.4 2.4-2.6-.6-.6-2.6z"/>',
    'droplet' => '<path d="M12 3s6 6.5 6 11a6 6 0 01-12 0c0-4.5 6-11 6-11z"/>',
    'plus' => '<rect x="4" y="4" width="16" height="16" rx="4"/><path d="M12 8v8M8 12h8"/>',
    'siren' => '<path d="M7 18v-6a5 5 0 0110 0v6"/><path d="M5 18h14v3H5zM12 3v2M4.2 6.2l1.4 1.4M19.8 6.2l-1.4 1.4"/>',
    'map' => '<path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2z"/><path d="M9 4v14M15 6v14"/>',
    'pin' => '<path d="M12 21s-7-6-7-11a7 7 0 0114 0c0 5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
    'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
    'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/>',
    'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
    'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
    'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0114 0M16 4a3.5 3.5 0 010 7M18 14a6 6 0 014 6"/>',
    'logout' => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h11"/>',
    'check' => '<path d="M5 12l5 5L20 7"/>',
    'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'alert' => '<path d="M12 3l9 16H3z"/><path d="M12 10v4M12 17h.01"/>',
    'eye-off' => '<path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.1A9.8 9.8 0 0112 5c6 0 10 7 10 7a17 17 0 01-3.2 3.9M6.6 6.6A17 17 0 002 12s4 7 10 7a9.6 9.6 0 004.3-1"/>',
    'camera' => '<path d="M4 7h3l2-3h6l2 3h3v13H4z"/><circle cx="12" cy="13" r="3.5"/>',
    'download' => '<path d="M12 4v12M7 11l5 5 5-5M4 20h16"/>',
    'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
    'x' => '<path d="M6 6l12 12M18 6L6 18"/>',
    'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/>',
    'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/>',
    'bolt' => '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>',
];
@endphp
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? $paths['shield'] !!}</svg>
