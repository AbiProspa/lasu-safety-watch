@props(['light' => false])
{{-- Official LASU Safety Watch crest (public/images/logo-mark.png). On dark backgrounds it sits on a white tile. --}}
<a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="LASU Safety Watch home">
    <span class="{{ $light ? 'rounded-xl bg-white p-1' : '' }} shrink-0">
        <img src="{{ asset('images/logo-mark.png') }}" alt="" width="40" height="41" class="h-10 w-auto">
    </span>
    <span class="leading-tight">
        <span class="block text-[15px] font-extrabold tracking-tight {{ $light ? 'text-white' : 'text-ink' }}">LASU Safety Watch</span>
        <span class="block text-[11px] font-medium {{ $light ? 'text-brand-200' : 'text-muted' }}">Lagos State University</span>
    </span>
</a>
