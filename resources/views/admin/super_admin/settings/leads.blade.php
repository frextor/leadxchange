@extends('admin.layouts.admin')
@section('title', 'Réglages des leads')
@section('page-title', 'Réglages des leads')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Réglages des leads</h1>
        <p class="text-sm text-gray-400 mt-1">Points gagnés à l'envoi et perdus à la réception d'un lead.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0">
        <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2">
        <form method="POST" action="{{ route('admin.super.settings.leads.update') }}">
            @csrf @method('PUT')

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-0.5">Barème des points</p>
                    <p class="text-xs text-gray-400">Appliqué quand un lead est accepté par le destinataire.</p>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                            <span class="inline-flex items-center gap-1.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                                Points gagnés à l'envoi
                            </span>
                        </label>
                        <p class="text-[11px] text-gray-400 mb-2">Crédités à l'expéditeur quand son lead est accepté.</p>
                        <div class="relative max-w-[160px]">
                            <input type="number" name="points_send_credit"
                                   value="{{ old('points_send_credit', $sendCredit) }}"
                                   min="0" max="100" step="1" required
                                   class="w-full h-10 pl-3 pr-10 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">pts</span>
                        </div>
                        @error('points_send_credit')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                            <span class="inline-flex items-center gap-1.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                                Points perdus à la réception
                            </span>
                        </label>
                        <p class="text-[11px] text-gray-400 mb-2">Débités du destinataire dès qu'il reçoit un lead.</p>
                        <div class="relative max-w-[160px]">
                            <input type="number" name="points_receive_debit"
                                   value="{{ old('points_receive_debit', $receiveDebit) }}"
                                   min="0" max="100" step="1" required
                                   class="w-full h-10 pl-3 pr-10 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">pts</span>
                        </div>
                        @error('points_receive_debit')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-xs text-amber-700">
                    <p class="font-semibold mb-1">⚠️ Lien avec les CGU</p>
                    <p>Ces montants sont actuellement cités dans les CGU (§6.2) comme des valeurs fixes (+2 / −1). Si vous les changez, pensez à mettre les CGU à jour pour rester cohérent.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 text-xs text-indigo-700 space-y-2">
            <p class="font-bold">À noter</p>
            <ul class="space-y-1.5 list-disc list-inside">
                <li>Un changement ne s'applique qu'aux leads acceptés/reçus après l'enregistrement.</li>
                <li>Le plafond de solde (30 pts) et le prix de rachat d'un point restent gérés ailleurs.</li>
            </ul>
            <a href="{{ route('admin.super.settings.points') }}" class="inline-flex items-center gap-1 mt-2 font-semibold text-indigo-600 hover:underline">
                Achat de points & facturation →
            </a>
        </div>
    </div>
</div>

@endsection
