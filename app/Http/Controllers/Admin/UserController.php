<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::withCount('reports')
            ->when($request->query('role'), fn ($q, $r) => $q->where('role', $r))
            ->when($request->query('q'), fn ($q, $v) => $q->where(fn ($w) => $w->where('name', 'like', "%$v%")->orWhere('email', 'like', "%$v%")))
            ->orderByRaw("case when role = 'student' then 1 else 0 end")->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(array_keys(config('lsw.roles')))],
            'password' => ['required', Password::min(8)->letters()->numbers()],
        ]);

        User::create($data);

        return back()->with('success', "Account created for {$data['name']}.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(config('lsw.roles')))],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', Password::min(8)->letters()->numbers()],
        ]);

        if ($user->is($request->user()) && ($data['role'] !== 'admin' || ! $data['is_active'])) {
            return back()->withErrors(['role' => 'You cannot remove your own admin access.']);
        }

        $user->role = $data['role'];
        $user->is_active = $data['is_active'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('success', "{$user->name} updated.");
    }
}
