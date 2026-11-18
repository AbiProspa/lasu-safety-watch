@extends('layouts.public')
@php $title = 'Create account'; @endphp

@section('content')
<div class="container-app max-w-lg py-14">
    <div class="card p-8">
        <h1 class="text-2xl font-extrabold text-slate-900">Create your account</h1>
        <p class="mt-1 text-sm text-slate-500">An account lets you see all your reports in one place. Reporting is still possible without one.</p>
        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="label" for="name">Full name</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="input">
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="matric_no">Matric / Staff no. <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="matric_no" name="matric_no" value="{{ old('matric_no') }}" class="input">
                </div>
                <div>
                    <label class="label" for="phone">Phone <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" class="input">
                </div>
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" type="password" name="password" required class="input">
                <p class="mt-1 text-xs text-slate-400">At least 8 characters, with letters and numbers.</p>
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="input">
            </div>
            <button class="btn-primary w-full py-3">Create account</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-500">Already registered? <a href="{{ route('login') }}" class="font-semibold text-brand-700">Log in</a></p>
    </div>
</div>
@endsection
