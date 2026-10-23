<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(Request $request): View
    {
        $codes = PromoCode::with(['creator:id,first_name,last_name', 'user:id,first_name,last_name,email'])
            ->latest()
            ->paginate(20);

        return view('admin.super_admin.promo-codes.index', compact('codes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'premium_days' => ['required', 'integer', 'min:1', 'max:365'],
            'label'        => ['nullable', 'string', 'max:150'],
            'quantity'     => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $quantity = (int) ($data['quantity'] ?? 1);
        $created  = [];

        for ($i = 0; $i < $quantity; $i++) {
            $created[] = PromoCode::create([
                'code'         => PromoCode::generateUniqueCode(),
                'premium_days' => $data['premium_days'],
                'label'        => $data['label'] ?? null,
                'created_by'   => $request->user()->id,
            ]);
        }

        ActivityLogger::log(
            'admin.promo_code.created',
            $quantity > 1
                ? "{$quantity} codes promo créés ({$data['premium_days']} jours de Premium)"
                : "Code promo \"{$created[0]->code}\" créé ({$data['premium_days']} jours de Premium)"
        );

        return back()->with('success', $quantity > 1
            ? "{$quantity} codes promo créés."
            : "Code promo \"{$created[0]->code}\" créé.");
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        if ($promoCode->isUsed()) {
            return back()->with('error', 'Impossible de supprimer un code déjà utilisé.');
        }

        $code = $promoCode->code;
        $promoCode->delete();

        ActivityLogger::log('admin.promo_code.deleted', "Code promo \"{$code}\" supprimé");

        return back()->with('success', 'Code promo supprimé.');
    }

    public function toggle(PromoCode $promoCode): RedirectResponse
    {
        if ($promoCode->isUsed()) {
            return back()->with('error', 'Impossible de désactiver un code déjà utilisé.');
        }

        $promoCode->update(['is_active' => ! $promoCode->is_active]);

        return back()->with('success', $promoCode->is_active ? 'Code réactivé.' : 'Code désactivé.');
    }
}
