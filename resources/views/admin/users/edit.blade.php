@extends('admin.layouts.admin')

@section('title', 'Modifier ' . $user->first_name)
@section('page-title', 'Modifier ' . $user->first_name . ' ' . $user->last_name)
@section('page-subtitle', 'Rôle, points, badge')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <div class="flex items-center gap-3 mb-6 pb-5 border-b border-gray-100">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold"
                 style="background:linear-gradient(135deg,#7181ED,#2F44E0);">
                {{ strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                <p class="text-xs text-gray-400">{{ $user->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rôle</label>
                <select name="role" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400">
                    <option value="user"        {{ $user->role === 'user'        ? 'selected' : '' }}>Utilisateur</option>
                    <option value="admin"       {{ $user->role === 'admin'       ? 'selected' : '' }}>Admin</option>
                    <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
                @error('role')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Solde de points</label>
                <input type="number" name="points_balance" value="{{ old('points_balance', $user->points_balance) }}" min="0"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400">
                @error('points_balance')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Badge</label>
                <select name="badge_level" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400">
                    <option value="bronze" {{ $user->badge_level === 'bronze' ? 'selected' : '' }}>Bronze</option>
                    <option value="argent" {{ $user->badge_level === 'argent' ? 'selected' : '' }}>Argent</option>
                    <option value="or"     {{ $user->badge_level === 'or'     ? 'selected' : '' }}>Or</option>
                </select>
                @error('badge_level')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#2F44E0;">Enregistrer</button>
                <a href="{{ route('admin.users.show', $user) }}" class="px-5 py-2.5 rounded-lg text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
