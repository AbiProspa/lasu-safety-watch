@props(['light' => false])
<a href="{{ route('home') }}" class="flex items-center gap-2.5">
    <span class="grid size-9 place-items-center rounded-xl bg-brand-700 text-white">
        <x-icon name="shield" class="size-5" />
    </span>
    <span class="leading-tight">
        <span class="block text-[15px] font-extrabold tracking-tight {{ $light ? 'text-white' : 'text-ink' }}">LASU Safety Watch</span>
        <span class="block text-[11px] font-medium {{ $light ? 'text-brand-200' : 'text-muted' }}">Lagos State University</span>
    </span>
</a>
