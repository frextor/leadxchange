<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Event;
use App\Models\Interest;
use App\Models\Language;
use App\Models\Nationality;
use App\Models\Plan;
use App\Models\Market;
use App\Models\Sector;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * GET /api/settings
     *
     * Returns all reference data in a single call.
     * Designed for mobile app bootstrap (load once, cache locally).
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'version'           => 'v2',
            'event_categories'  => collect(Event::$categoryLabels)
                                       ->map(fn($label, $key) => ['key' => $key, 'label' => $label])
                                       ->values(),
            'interests'     => Interest::orderBy('name')
                                   ->get(['id', 'name', 'icon']),

            'sectors'       => Sector::orderBy('name')
                                   ->get(['id', 'name']),

            'markets'       => Market::orderBy('name')
                                   ->get(['id', 'name']),

            'languages'     => Language::orderBy('name')
                                   ->get(['id', 'name', 'native_name', 'code']),

            'nationalities' => Nationality::orderBy('country')
                                   ->get(['id', 'name', 'country', 'code', 'flag']),

            'countries'     => Country::orderBy('name')
                                   ->get(['id', 'name', 'code', 'flag']),

            'plans'         => Plan::where('is_active', true)->where('is_visible', true)
                                   ->orderBy('sort_order')
                                   ->get(['id', 'name', 'label', 'description', 'price', 'billing_period', 'max_leads', 'max_groups', 'max_users', 'features', 'permissions'])
                                   ->map(fn($plan) => [
                                       'id'             => $plan->id,
                                       'name'           => $plan->name,
                                       'label'          => $plan->label,
                                       'description'    => $plan->description,
                                       'price'          => $plan->price,
                                       'billing_period' => $plan->billing_period,
                                       'max_leads'      => $plan->max_leads,
                                       'max_groups'     => $plan->max_groups,
                                       'max_users'      => $plan->max_users,
                                       'features'       => $this->planFeaturesToArray($plan->features),
                                       'permissions'    => $this->planPermissionsToArray($plan->permissions),
                                   ]),

            'cities'        => City::where('is_active', true)->with('country:id,name,code,flag')
                                   ->orderByRaw("CASE WHEN country_id = (SELECT id FROM countries WHERE code = 'MA') THEN 0 ELSE 1 END")
                                   ->orderBy('name')
                                   ->get(['id', 'name', 'country_id'])
                                   ->map(fn($city) => [
                                       'id'           => $city->id,
                                       'name'         => $city->name,
                                       'country_id'   => $city->country_id,
                                       'country_name' => $city->country?->name,
                                       'country_code' => $city->country?->code,
                                       'flag'         => $city->country?->flag,
                                   ]),
        ]);
    }

    private function planFeaturesToArray(mixed $features): array
    {
        if (empty($features)) return [];

        // Already a flat string array — return as-is
        if (array_is_list($features) && isset($features[0]) && is_string($features[0])) {
            return $features;
        }

        // Associative object — convert to display strings
        $result = [];
        $maxConn = $features['max_connections_per_month'] ?? null;
        if ($maxConn === null) {
            $result[] = 'Unlimited connections';
        } elseif ($maxConn > 0) {
            $result[] = "{$maxConn} connections/month";
        }

        $labels = [
            'public_listing'    => 'Public listing',
            'basic_profile'     => 'Basic profile',
            'view_profile_info' => 'Full profile access',
            'priority_listing'  => 'Priority listing',
            'analytics'         => 'Analytics',
            'send_leads'        => 'Send leads',
            'create_events'     => 'Create events',
            'featured_profile'  => 'Featured profile',
            'dedicated_support' => 'Dedicated support',
        ];

        foreach ($labels as $key => $display) {
            if (!empty($features[$key]) && $features[$key] === true) {
                $result[] = $display;
            }
        }

        return $result;
    }

    private function planPermissionsToArray(mixed $permissions): array
    {
        if (empty($permissions)) return [];

        // Already a flat string array — return as-is
        if (array_is_list($permissions) && isset($permissions[0]) && is_string($permissions[0])) {
            return $permissions;
        }

        $result = [];

        // Numeric limits — show as readable strings
        $maxLeads = $permissions['max_leads_per_month'] ?? null;
        if ($maxLeads === null) {
            $result[] = 'Leads illimités/mois';
        } elseif ($maxLeads > 0) {
            $result[] = "{$maxLeads} leads/mois";
        }

        if (($permissions['mail_reply_weekly_limit'] ?? null) === null && !empty($permissions['can_send_mail'])) {
            $result[] = 'Messages illimités';
        }

        $maxGroups = $permissions['max_groups_joined'] ?? null;
        if ($maxGroups === null && !empty($permissions['can_join_pole'])) {
            $result[] = 'Groupes illimités';
        }

        // Boolean permissions — only show enabled ones
        $labels = [
            'can_view_member_contact'      => 'Voir les contacts membres',
            'can_view_member_name'         => 'Voir le nom des membres',
            'can_send_invitations'         => 'Envoyer des invitations',
            'can_send_mail'                => 'Messagerie directe',
            'can_send_sql'                 => 'Leads SQL',
            'can_send_sp'                  => 'Leads SP',
            'can_join_pole'                => 'Rejoindre des groupes',
            'can_create_pole'              => 'Créer des groupes',
            'can_create_events'            => 'Créer des événements',
            'can_organize_regional_events' => 'Événements régionaux',
            'can_nominate_consul'          => 'Nommer des consuls',
            'can_add_member'               => 'Ajouter des membres',
        ];

        foreach ($labels as $key => $display) {
            if (!empty($permissions[$key]) && $permissions[$key] === true) {
                $result[] = $display;
            }
        }

        return $result;
    }
}
