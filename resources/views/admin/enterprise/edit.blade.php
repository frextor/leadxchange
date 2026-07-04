@extends('admin.layouts.admin')
@section('title', 'Modifier le pack — ' . $license->company_name)

@section('content')
<div class="p-6 max-w-3xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('admin.super.enterprise.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">← Retour aux licences</a>
        <h1 class="text-xl font-bold text-gray-900 mt-2">Pack : {{ $license->company_name }}</h1>
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

            {{-- Company name --}}
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                    Nom de l'entreprise <span class="text-red-500">*</span>
                </label>
                <input type="text" name="company_name" required
                       value="{{ old('company_name', $license->company_name) }}"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                       style="--tw-ring-color:#6366F1;">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    @php $usedSeats = 1 + $license->invitations->whereIn('status', ['pending','active'])->count(); @endphp
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                        Nombre de licences <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="seats_total"
                           min="{{ $usedSeats }}" max="500"
                           value="{{ old('seats_total', $license->seats_total) }}" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color:#6366F1;">
                    <p class="text-xs text-gray-400 mt-1">Min. {{ $usedSeats }} (sièges effectivement utilisés).</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Expiration</label>
                    <input type="date" name="expires_at"
                           value="{{ old('expires_at', $license->expires_at?->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color:#6366F1;">
                    <p class="text-xs text-gray-400 mt-1">Vide = sans expiration.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Notes internes</label>
                <textarea name="notes" rows="2"
                          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 resize-none"
                          style="--tw-ring-color:#6366F1;">{{ old('notes', $license->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <div class="text-sm text-gray-500">
                    <span class="font-semibold text-indigo-600">{{ $usedSeats }}</span> / {{ $license->seats_total }} utilisées
                    &nbsp;·&nbsp;
                    <span class="text-emerald-600 font-semibold">{{ $license->seatsAvailable() }}</span> disponibles
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

    {{-- All invitation slots --}}
    @php
        $grouped = $license->invitations->groupBy('status');
        $sortOrder = ['active' => 0, 'pending' => 1, 'available' => 2, 'revoked' => 3];
        $sorted = $license->invitations->sortBy(fn($i) => $sortOrder[$i->status] ?? 9);
    @endphp
    @if($license->invitations->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-700">Toutes les licences</h2>
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold">{{ $grouped['active']->count() ?? 0 }} actives</span>
                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-semibold">{{ $grouped['pending']->count() ?? 0 }} en attente</span>
                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-semibold">{{ $grouped['available']->count() ?? 0 }} disponibles</span>
            </div>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left border-b border-gray-100">
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Membre</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Lien d'activation</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sorted as $inv)
                <tr class="border-b border-gray-50 hover:bg-gray-50 {{ $loop->last ? 'border-0' : '' }}">
                    <td class="px-5 py-3">
                        @if($inv->user)
                            <p class="font-semibold text-gray-900">{{ $inv->user->first_name }} {{ $inv->user->last_name }}</p>
                            <p class="text-xs text-gray-400">{{ $inv->user->email }}</p>
                        @elseif($inv->email)
                            <p class="text-gray-600">{{ $inv->email }}</p>
                            <p class="text-xs text-gray-400">En attente d'activation</p>
                        @else
                            <p class="text-gray-400 italic text-xs">Non assignée</p>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($inv->status === 'active')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Actif</span>
                        @elseif($inv->status === 'pending')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">En attente</span>
                        @elseif($inv->status === 'available')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Disponible</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500">Révoqué</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if(in_array($inv->status, ['available', 'pending']))
                        <div class="flex items-center gap-1.5">
                            <input type="text" readonly value="{{ route('enterprise.join', $inv->token) }}"
                                   class="text-[11px] bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 w-56 text-gray-500 font-mono"
                                   onclick="this.select()">
                        </div>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">
                        @if($inv->accepted_at)
                            Rejoint le {{ $inv->accepted_at->format('d/m/Y') }}
                        @else
                            {{ $inv->created_at->format('d/m/Y') }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection
