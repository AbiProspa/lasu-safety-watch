@extends('layouts.public')
@php $title = 'Log in'; @endphp

@section('content')
<div class="container-app grid max-w-5xl items-center gap-10 py-14 lg:grid-cols-2">
    <div class="hidden lg:block">
        <div class="rounded-3xl bg-gradient-to-br from-brand-800 to-brand-950 p-10 text-white">
            <x-icon name="shield" class="size-12 text-brand-200" />
            <h2 class="mt-6 text-3xl font-extrabold">Welcome back</h2>
            <p class="mt-3 text-brand-100/80">Students track their reports. Security, Works, Medical and Counselling staff manage incidents from the dashboard.</p>
            <ul class="mt-8 space-y-3 text-sm text-brand-100/90">
                <li class="flex gap-2"><x-icon name="check" class="size-5 text-brand-500" />Live SOS alerts for Campus Security</li>
                <li class="flex gap-2"><x-icon name="check" class="size-5 text-brand-500" />Verify, assign and resolve reports</li>
                <li class="flex gap-2"><x-icon name="check" class="size-5 text-brand-500" />Charts, hotspots and CSV export</li>
            </ul>
        </div>
    </div>
    <div class="card p-8">
        <h1 class="text-2xl font-extrabold text-slate-900">Log in</h1>
        <p class="mt-1 text-sm text-slate-500">You don't need an account to report an incident.</p>
        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" type="password" name="password" required class="input">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-700"> Remember me
            </label>
            <button class="btn-primary w-full py-3">Log in</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-500">New here? <a href="{{ route('register') }}" class="font-semibold text-brand-700">Create a student account</a></p>
    </div>
</div>
@endsection
