@extends('layouts.public')
@php $title = 'Report an incident'; @endphp

@section('content')
@php
    $catJson = $categories->mapWithKeys(fn ($c) => [$c->id => ['subtypes' => $c->subtypes ?? [], 'confidential' => $c->is_confidential, 'color' => $c->color]]);
    $initialCat = old('category_id', $selected ?? '');
@endphp
<div class="container-app max-w-4xl py-10">
    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-700">New report</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Report an incident</h1>
        <p class="mt-2 text-slate-500">Takes about a minute. If someone is in immediate danger, use the <a href="{{ route('safety') }}#sos" class="font-semibold text-red-600 underline">SOS button</a> or call 112.</p>
    </div>

    <x-flash />

    <form method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data"
          x-data="{ cat: '{{ $initialCat }}', cats: {{ Js::from($catJson) }}, anon: {{ old('is_anonymous') ? 'true' : 'false' }}, preview: null,
                    get current() { return this.cats[this.cat] || null } }"
          x-effect="if (current && current.confidential) anon = true"
          class="space-y-6">
        @csrf

        {{-- Step 1: type --}}
        <div class="card p-6">
            <h2 class="flex items-center gap-2 font-bold text-slate-900"><span class="grid size-6 place-items-center rounded-full bg-brand-700 text-xs text-white">1</span> What kind of incident?</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $c)
                    <label class="relative flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3 transition"
                           :class="cat == '{{ $c->id }}' ? 'border-brand-600 bg-brand-50' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="category_id" value="{{ $c->id }}" x-model="cat" class="sr-only">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl text-white" style="background: {{ $c->color }}"><x-icon :name="$c->icon" /></span>
                        <span class="text-sm font-semibold leading-tight text-slate-800">{{ $c->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('category_id')<p class="error">{{ $message }}</p>@enderror

            <div class="mt-5 grid gap-4 sm:grid-cols-2" x-show="current" x-cloak>
                <div x-show="current && current.subtypes.length">
                    <label class="label" for="subtype">Specific type</label>
                    <select id="subtype" name="subtype" class="input">
                        <option value="">Select…</option>
                        <template x-for="s in (current ? current.subtypes : [])" :key="s">
                            <option :value="s" x-text="s" :selected="s === '{{ old('subtype') }}'"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <span class="label">How serious is it?</span>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach (config('lsw.severities') as $key => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="severity" value="{{ $key }}" class="peer sr-only" @checked(old('severity', 'medium') === $key)>
                                <span class="block rounded-lg border border-slate-200 py-2 text-center text-xs font-semibold text-slate-600 transition peer-checked:border-transparent
                                    {{ ['low' => 'peer-checked:bg-slate-700', 'medium' => 'peer-checked:bg-yellow-500', 'high' => 'peer-checked:bg-orange-500', 'critical' => 'peer-checked:bg-red-600'][$key] }} peer-checked:text-white">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: details --}}
        <div class="card p-6">
            <h2 class="flex items-center gap-2 font-bold text-slate-900"><span class="grid size-6 place-items-center rounded-full bg-brand-700 text-xs text-white">2</span> What happened?</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="label" for="title">Short title</label>
                    <input id="title" name="title" value="{{ old('title') }}" maxlength="150" required class="input" placeholder="e.g. Phone snatched near Faculty of Arts">
                    @error('title')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="description">Description</label>
                    <textarea id="description" name="description" rows="5" required class="input" placeholder="What happened, who was involved, what they looked like, which direction they went…">{{ old('description') }}</textarea>
                    @error('description')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="occurred_at">When did it happen?</label>
                        <input id="occurred_at" type="datetime-local" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" class="input">
                    </div>
                    <div>
                        <span class="label">Photo evidence (optional)</span>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-slate-300 px-4 py-2.5 text-sm text-slate-500 hover:border-brand-600 hover:text-brand-700">
                            <template x-if="preview"><img :src="preview" class="size-10 rounded-lg object-cover"></template>
                            <x-icon name="camera" x-show="!preview" />
                            <span x-text="preview ? 'Photo selected (tap to change)' : 'Upload a photo (max 5MB)'"></span>
                            <input type="file" name="photo" accept="image/*" class="sr-only" @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        </label>
                        @error('photo')<p class="error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: location --}}
        <div class="card p-6" x-data="locationPicker({{ Js::from(config('lsw.campus_center')) }}, {{ Js::from(config('lsw.locations')) }}, { lat: '{{ old('latitude') }}', lng: '{{ old('longitude') }}', name: '{{ old('location_name') }}' })">
            <h2 class="flex items-center gap-2 font-bold text-slate-900"><span class="grid size-6 place-items-center rounded-full bg-brand-700 text-xs text-white">3</span> Where did it happen?</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-[1fr_auto]">
                <div>
                    <label class="label" for="location_name">Campus location</label>
                    <select id="location_name" name="location_name" x-model="locationName" required class="input">
                        <option value="">Choose a landmark…</option>
                        @foreach (array_keys(config('lsw.locations')) as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                    @error('location_name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end">
                    <button type="button" @click="useMyLocation()" class="btn-light w-full" :disabled="locating">
                        <x-icon name="pin" class="size-4" /><span x-text="locating ? 'Locating…' : 'Use my location'"></span>
                    </button>
                </div>
            </div>
            <div x-ref="map" class="mt-4 h-72 w-full overflow-hidden rounded-xl border border-slate-200"></div>
            <p class="mt-2 text-xs text-slate-500" x-text="message || 'Tip: tap the map to drop an exact pin. You can drag it.'"></p>
            <input type="hidden" name="latitude" :value="lat">
            <input type="hidden" name="longitude" :value="lng">
        </div>

        {{-- Step 4: privacy --}}
        <div class="card p-6">
            <h2 class="flex items-center gap-2 font-bold text-slate-900"><span class="grid size-6 place-items-center rounded-full bg-brand-700 text-xs text-white">4</span> Your privacy</h2>
            <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50 p-4">
                <input type="hidden" name="is_anonymous" value="0">
                <input type="checkbox" name="is_anonymous" value="1" x-model="anon" :disabled="current && current.confidential" class="mt-0.5 size-5 rounded border-slate-300 text-brand-700 focus:ring-brand-600">
                <span>
                    <span class="flex items-center gap-1.5 font-semibold text-slate-800"><x-icon name="eye-off" class="size-4" />Report anonymously</span>
                    <span class="mt-0.5 block text-sm text-slate-500">Your name will not be stored with this report. You can still track it with your code.
                        <template x-if="current && current.confidential"><b class="text-purple-700"> Harassment & GBV reports are always anonymous.</b></template>
                    </span>
                </span>
            </label>
            <template x-if="current && current.confidential"><input type="hidden" name="is_anonymous" value="1"></template>
            <div class="mt-4">
                <label class="label" for="reporter_contact">Contact for follow-up (optional)</label>
                <input id="reporter_contact" name="reporter_contact" value="{{ old('reporter_contact') }}" class="input" placeholder="Phone or email. Only the responding unit can see this.">
            </div>
            @guest
                <p class="mt-4 text-sm text-slate-500">Want to see all your reports in one place? <a href="{{ route('register') }}" class="font-semibold text-brand-700 underline">Create an account</a> first (optional).</p>
            @endguest
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('home') }}" class="btn-light">Cancel</a>
            <button class="btn-primary px-6 py-3 text-base"><x-icon name="check" />Submit report</button>
        </div>
    </form>
</div>
@endsection
