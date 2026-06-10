<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminManagerController extends Controller
{
    public function index(): View
    {
        $admins = User::whereIn('role', ['admin', 'super_admin'])
            ->orderByRaw("FIELD(role, 'super_admin', 'admin')")
            ->orderBy('first_name')
            ->get();

        return view('admin.super_admin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.super_admin.admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:200', 'unique:users,email'],
            'password'   => ['required', Password::min(8)->letters()->numbers()],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(User::ADMIN_PERMISSIONS))],
        ]);

        User::create([
            'first_name'         => $validated['first_name'],
            'last_name'          => $validated['last_name'],
            'email'              => $validated['email'],
            'password'           => Hash::make($validated['password']),
            'role'               => 'admin',
            'admin_permissions'  => $validated['permissions'] ?? null,
            'email_verified_at'  => now(),
            'onboarding_completed' => true,
        ]);

        return redirect()->route('admin.super.admins.index')
            ->with('success', "Admin {$validated['first_name']} {$validated['last_name']} créé.");
    }

    /** Promote an existing regular user to admin. */
    public function promote(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'     => ['required', 'exists:users,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(User::ADMIN_PERMISSIONS))],
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Impossible de modifier un super administrateur.');
        }

        $user->update([
            'role'              => 'admin',
            'admin_permissions' => $request->permissions ?? null,
        ]);

        return redirect()->route('admin.super.admins.index')
            ->with('success', "{$user->first_name} {$user->last_name} promu administrateur.");
    }

    public function edit(User $user): View
    {
        abort_if($user->isSuperAdmin() && auth()->id() !== $user->id, 403);

        return view('admin.super_admin.admins.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin() && auth()->id() !== $user->id, 403);

        $rules = [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(User::ADMIN_PERMISSIONS))],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', Password::min(8)->letters()->numbers(), 'confirmed'];
        }

        $validated = $request->validate($rules);

        $data = ['admin_permissions' => $validated['permissions'] ?? null];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', 'Permissions mises à jour.');
    }

    public function demote(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Impossible de rétrograder un super administrateur.');
        }
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous rétrograder vous-même.');
        }

        $user->update(['role' => 'user', 'admin_permissions' => null]);

        return redirect()->route('admin.super.admins.index')
            ->with('success', "{$user->first_name} {$user->last_name} rétrogradé en utilisateur.");
    }
}
