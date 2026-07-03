@extends('admin.layouts.admin')
@section('title', 'Modifier la licence Entreprise')

@section('content')
<div class="p-6 max-w-2xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('admin.super.enterprise.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">← Retour aux licences</a>
        <h1 class="text-xl font-bold text-gray-900 mt-2">Modifier la licence</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            Titulaire : <strong>{{ $license->holder?->first_name }} {{ $license->holder?->last_name }}</strong>
            ({{ $license->holder?->email }})
        </p>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
        <form method="POST" action="{{ route('admin.super.enterprise.update', $license->id) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                        Nombre de licences <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="seats_total"
                           min="{{ $license->seats_used }}" max="500"
                           value="{{ old('seats_total', $license->seats_total) }}" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color:#6366F1;">
                    <p class="text-xs text-gray-400 mt-1">Min. {{ $license->seats_used }} (sièges déjà utilisés).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Expiration</label>
                    <input type="date" name="expires_at"
                           value="{{ old('expires_at', $license->expires_at?->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color:#6366F1;">
                    <p class="text-xs text-gray-400 mt-1">Laisser vide = sans expiration.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Notes internes</label>
                <textarea name="notes" rows="3"
                          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 resize-none"
                          style="--tw-ring-color:#6366F1;">{{ old('notes', $license->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <div class="text-sm text-gray-500">
                    Sièges : <strong class="text-indigo-600">{{ $license->seats_used }}</strong> / {{ $license->seats_total }} utilisés
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.super.enterprise.index') }}"
                       class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Members list --}}
    @if($license->invitations->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-700">Membres invités</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left border-b border-gray-100">
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Membre</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Invité le</th>
                </tr>
            </thead>
            <tbody>
                @foreach($license->invitations as $inv)
                <tr class="border-b border-gray-50 hover:bg-gray-50 {{ $loop->last ? 'border-0' : '' }}">
                    <td class="px-5 py-3">
                        @if($inv->user)
                            <p class="font-semibold text-gray-900">{{ $inv->user->first_name }} {{ $inv->user->last_name }}</p>
                            <p class="text-xs text-gray-400">{{ $inv->user->email }}</p>
                        @else
                            <p class="text-gray-500">{{ $inv->email }}</p>
                            <p class="text-xs text-gray-400">Compte non créé</p>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($inv->status === 'active')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Actif</span>
                        @elseif($inv->status === 'pending')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">En attente</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500">{{ ucfirst($inv->status) }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $inv->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection
