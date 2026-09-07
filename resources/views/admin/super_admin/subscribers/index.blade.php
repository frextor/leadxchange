@extends('admin.layouts.admin')
@section('title', 'Abonnés')
@section('page-title', 'Gestion des abonnés')
@section('page-subtitle', 'Liste et filtres des utilisateurs inscrits')

@section('content')
<div class="py-6 space-y-4">

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.super.subscribers.index') }}"
          class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email…"
                   class="w-full h-9 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-teal-400 transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Pays</label>
            <select name="country_id" class="h-9 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-teal-400 transition" style="appearance:none;min-width:120px;">
                <option value="">Tous</option>
                @foreach($countries as $c)
                <option value="{{ $c->id }}" {{ request('country_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Ville</label>
            <select name="city_id" class="h-9 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-teal-400 transition" style="appearance:none;min-width:140px;">
                <option value="">Toutes</option>
                @foreach($cities as $c)
                <option value="{{ $c->id }}" {{ request('city_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Plan</label>
            <select name="plan" class="h-9 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-teal-400 transition" style="appearance:none;min-width:120px;">
                <option value="">Tous</option>
                @foreach($plans as $p)
                <option value="{{ $p->name }}" {{ request('plan') === $p->name ? 'selected' : '' }}>{{ $p->label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Statut</label>
            <select name="status" class="h-9 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-teal-400 transition" style="appearance:none;min-width:120px;">
                <option value="">Tous</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Abonné actif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Sans abonnement</option>
            </select>
        </div>
        <button type="submit"
                class="h-9 px-4 rounded-xl text-sm font-semibold text-white"
                style="background:#2F44E0;">Filtrer</button>
        @if(request()->hasAny(['search','country_id','city_id','plan','status']))
        <a href="{{ route('admin.super.subscribers.index') }}"
           class="h-9 px-4 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition flex items-center">
            Réinitialiser
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm text-gray-500">{{ $subscribers->total() }} résultat{{ $subscribers->total() > 1 ? 's' : '' }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Utilisateur</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Ville / Pays</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Plan</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Inscription</th>
                        <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscribers as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                     style="background:linear-gradient(135deg,#7181ED,#2F44E0);">
                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <p class="text-sm">{{ $user->city?->name ?? '—' }}</p>
                            @if($user->city?->country)
                            <p class="text-xs text-gray-400">{{ $user->city->country->name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($user->subscription?->plan)
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                  style="background:#EEF2FF;color:#4F46E5;">
                                {{ $user->subscription->plan->label }}
                            </span>
                            @else
                            <span class="text-xs text-gray-400">Basic</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($user->subscription?->status === 'active')
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Actif</span>
                            @else
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Aucun résultat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscribers->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $subscribers->links() }}</div>
        @endif
    </div>

</div>
@endsection
