@extends('layouts.public')
@php $title = 'Safety Center'; @endphp

@section('content')
<div class="container-app py-10">
    <div class="max-w-2xl">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-700">Safety Center</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Help, right when you need it</h1>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-5">
        {{-- SOS --}}
        <section id="sos" class="scroll-mt-24 lg:col-span-3" x-data="sosButton('{{ route('sos') }}')">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-600 to-red-800 p-8 text-white shadow-xl">
                <div class="absolute -right-16 -top-16 size-64 rounded-full bg-white/10"></div>
                <div class="relative">
                    <h2 class="flex items-center gap-2 text-2xl font-extrabold"><x-icon name="siren" class="size-7" />Emergency SOS</h2>
                    <p class="mt-2 max-w-md text-red-100">Press the button if you are in danger. After a 5-second countdown, your GPS location is sent to Campus Security. No login needed.</p>

                    <div class="mt-8 flex flex-col items-center gap-6 sm:flex-row sm:items-center">
                        {{-- idle --}}
                        <template x-if="stage === 'idle'">
                            <button type="button" @click="start()" class="sos-pulse grid size-40 shrink-0 place-items-center rounded-full border-8 border-white/30 bg-white text-3xl font-black tracking-widest text-red-600 shadow-2xl transition hover:scale-105">SOS</button>
                        </template>
                        {{-- countdown --}}
                        <template x-if="stage === 'countdown'">
                            <div class="grid size-40 shrink-0 place-items-center rounded-full border-8 border-white/30 bg-red-900/40">
                                <span class="text-6xl font-black" x-text="count"></span>
                            </div>
                        </template>
                        <template x-if="stage === 'sending'">
                            <div class="grid size-40 shrink-0 place-items-center rounded-full border-8 border-white/30 bg-red-900/40 text-center text-sm font-bold">Sending…</div>
                        </template>
                        <template x-if="stage === 'sent' || stage === 'error'">
                            <div class="grid size-40 shrink-0 place-items-center rounded-full border-8 border-white/30 bg-white text-red-600">
                                <x-icon name="check" class="size-16" x-show="stage === 'sent'" /><x-icon name="alert" class="size-16" x-show="stage === 'error'" />
                            </div>
                        </template>

                        <div class="w-full flex-1">
                            <div x-show="stage === 'idle'">
                                <label class="text-sm font-semibold text-red-100" for="note">What is happening? (optional)</label>
                                <textarea id="note" x-model="note" rows="3" maxlength="300" class="mt-1.5 w-full rounded-xl border-0 bg-white/15 px-3 py-2 text-sm text-white placeholder:text-red-200 focus:ring-2 focus:ring-white" placeholder="e.g. Fight at Hostel B gate"></textarea>
                            </div>
                            <div x-show="stage === 'countdown'" x-cloak>
                                <p class="text-lg font-bold">Sending alert in <span x-text="count"></span>s…</p>
                                <div class="mt-3 flex gap-2">
                                    <button @click="send()" class="btn bg-white text-red-700 hover:bg-red-50">Send now</button>
                                    <button @click="cancel()" class="btn border border-white/40 text-white hover:bg-white/10">Cancel</button>
                                </div>
                            </div>
                            <div x-show="stage === 'sending'" x-cloak><p class="text-lg font-bold">Getting your location and alerting Security…</p></div>
                            <div x-show="stage === 'sent'" x-cloak>
                                <p class="text-lg font-bold">Alert sent to Campus Security.</p>
                                <p class="mt-1 text-sm text-red-100" x-text="exact ? 'Your exact GPS location was shared. Stay where you are if it is safe.' : 'Your exact location could not be read. Please also call Campus Security.'"></p>
                                <p class="mt-2 text-sm">Reference: <b class="font-mono" x-text="reference"></b></p>
                                <button @click="stage = 'idle'" class="btn mt-3 border border-white/40 text-white hover:bg-white/10">Done</button>
                            </div>
                            <div x-show="stage === 'error'" x-cloak>
                                <p class="text-lg font-bold">Alert could not be sent.</p>
                                <p class="mt-1 text-sm text-red-100" x-text="reference + ' Call the numbers on this page.'"></p>
                                <button @click="stage = 'idle'" class="btn mt-3 bg-white text-red-700">Try again</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Contacts --}}
        <section class="card p-6 lg:col-span-2">
            <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900"><x-icon name="phone" />Emergency contacts</h2>
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach (config('lsw.contacts') as $c)
                    <li class="flex items-center gap-3 py-3">
                        <span class="h-10 w-1 rounded-full {{ ['critical' => 'bg-red-500', 'high' => 'bg-amber-400', 'normal' => 'bg-slate-300'][$c['level']] }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $c['name'] }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $c['desc'] }}</p>
                        </div>
                        <a href="tel:{{ $c['phone'] }}" class="btn-primary shrink-0 px-3 py-2 text-xs"><x-icon name="phone" class="size-3.5" />{{ $c['phone'] }}</a>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    {{-- Tips --}}
    <section class="mt-10">
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Safety tips</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach (config('lsw.tips') as $heading => $tips)
                <div class="card p-5" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between text-left font-bold text-slate-900">
                        {{ $heading }}
                        <span class="grid size-7 place-items-center rounded-full bg-slate-100 text-slate-500 transition" :class="open && 'rotate-45'">+</span>
                    </button>
                    <ul x-show="open" x-transition class="mt-3 space-y-2">
                        @foreach ($tips as $tip)
                            <li class="flex gap-2 text-sm text-slate-600"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-brand-600" />{{ $tip }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
