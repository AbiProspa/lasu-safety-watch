@extends('layouts.staff')
@php $title = 'Users & roles'; @endphp

@section('content')
<div class="grid gap-6 xl:grid-cols-3">
    <div class="xl:col-span-2">
        <form method="GET" class="card mb-4 flex flex-wrap gap-3 p-4">
            <input name="q" value="{{ request('q') }}" placeholder="Search name or email" class="input max-w-xs flex-1">
            <select name="role" class="input w-auto">
                <option value="">All roles</option>
                @foreach (config('lsw.roles') as $k => [$label]) <option value="{{ $k }}" @selected(request('role') === $k)>{{ $label }}</option>@endforeach
            </select>
            <button class="btn-primary">Filter</button>
        </form>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr><th class="px-5 py-3">User</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Reports</th><th class="px-5 py-3"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $u)
                            <tr x-data="{ edit: false }">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 place-items-center rounded-full bg-brand-100 text-xs font-bold text-brand-800">{{ $u->initials() }}</span>
                                        <div><p class="font-semibold text-slate-900">{{ $u->name }}</p><p class="text-xs text-slate-500">{{ $u->email }}</p></div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5" colspan="1">
                                    <span x-show="!edit" class="badge {{ $u->role === 'admin' ? 'bg-brand-700 text-white' : ($u->isStaff() ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-600') }}">{{ $u->roleLabel() }}</span>
                                    <form x-show="edit" x-cloak method="POST" action="{{ route('admin.users.update', $u) }}" class="flex flex-wrap gap-2">
                                        @csrf @method('PATCH')
                                        <select name="role" class="input py-1.5 text-xs">@foreach (config('lsw.roles') as $k => [$label])<option value="{{ $k }}" @selected($u->role === $k)>{{ $label }}</option>@endforeach</select>
                                        <select name="is_active" class="input py-1.5 text-xs"><option value="1" @selected($u->is_active)>Active</option><option value="0" @selected(! $u->is_active)>Disabled</option></select>
                                        <input name="password" type="password" placeholder="New password (optional)" class="input py-1.5 text-xs">
                                        <button class="btn-primary py-1.5 text-xs">Save</button>
                                    </form>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($u->is_active)<span class="badge bg-emerald-50 text-emerald-700">Active</span>@else<span class="badge bg-slate-100 text-slate-500">Disabled</span>@endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $u->reports_count }}</td>
                                <td class="px-5 py-3.5 text-right"><button type="button" @click="edit = !edit" class="text-sm font-semibold text-brand-700" x-text="edit ? 'Cancel' : 'Edit'"></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">{{ $users->links() }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="card h-fit space-y-4 p-6">
        @csrf
        <h3 class="font-bold text-slate-900">Add staff account</h3>
        <p class="-mt-2 text-sm text-slate-500">Create logins for Security, Works, Medical or Counselling officers.</p>
        <div><label class="label">Full name</label><input name="name" value="{{ old('name') }}" required class="input"></div>
        <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="input"></div>
        <div><label class="label">Phone</label><input name="phone" value="{{ old('phone') }}" class="input"></div>
        <div>
            <label class="label">Role</label>
            <select name="role" class="input">@foreach (config('lsw.roles') as $k => [$label])<option value="{{ $k }}" @selected(old('role', 'security') === $k)>{{ $label }}</option>@endforeach</select>
        </div>
        <div><label class="label">Temporary password</label><input type="text" name="password" required class="input" placeholder="At least 8 chars, letters + numbers"></div>
        <button class="btn-primary w-full">Create account</button>
    </form>
</div>
@endsection
