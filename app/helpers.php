<?php

use App\Models\SystemSetting;

if (! function_exists('route_exists')) {
    function route_exists(string $name): bool
    {
        return \Illuminate\Support\Facades\Route::has($name);
    }
}

if (! function_exists('currency_format')) {
    /**
     * Format a monetary amount using the platform currency settings.
     *
     * @param  float|int|string $amount
     * @param  int|null         $decimals  Override decimal places (null = use setting)
     */
    function currency_format(float|int|string $amount, ?int $decimals = null): string
    {
        $symbol   = SystemSetting::get('currency_symbol', '€');
        $position = SystemSetting::get('currency_position', 'after');
        $decs     = $decimals ?? (int) SystemSetting::get('currency_decimals', 0);
        $thou     = SystemSetting::get('currency_thousands_sep', ' ');
        $dec      = SystemSetting::get('currency_decimal_sep', '.');

        $formatted = number_format((float) $amount, $decs, $dec, $thou);

        return $position === 'before'
            ? $symbol . ' ' . $formatted
            : $formatted . ' ' . $symbol;
    }
}

if (! function_exists('currency_symbol')) {
    function currency_symbol(): string
    {
        return SystemSetting::get('currency_symbol', '€');
    }
}

/**
 * Display a member's name respecting the viewer's plan permission.
 * Basic plan → first name only. Prémium+ → full name.
 */
if (! function_exists('member_name')) {
    function member_name(\App\Models\User $member, bool $initialsOnly = false): string
    {
        /** @var \App\Models\User|null $viewer */
        $viewer = auth()->user();
        $canSeeLastName = !$viewer || $viewer->canFeature('can_view_member_name');

        if ($initialsOnly) {
            $last = $canSeeLastName ? mb_substr($member->last_name, 0, 1) : '';
            return strtoupper(mb_substr($member->first_name, 0, 1) . $last);
        }

        return $canSeeLastName
            ? $member->first_name . ' ' . $member->last_name
            : $member->first_name;
    }
}
