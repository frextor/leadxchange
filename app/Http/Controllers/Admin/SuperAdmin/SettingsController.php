<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\ActivityLogger;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function currency(): View
    {
        $settings = SystemSetting::where('group', 'currency')->get()->keyBy('key');

        return view('admin.super_admin.settings.currency', compact('settings'));
    }

    public function updateCurrency(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'currency_symbol'       => ['required', 'string', 'max:10'],
            'currency_position'     => ['required', 'in:before,after'],
            'currency_decimals'     => ['required', 'integer', 'in:0,2'],
            'currency_thousands_sep'=> ['required', 'string', 'max:3'],
            'currency_decimal_sep'  => ['required', 'string', 'max:3'],
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value);
        }

        Cache::forget('system_settings');
        ActivityLogger::log('admin.settings.updated', "Paramètres de devise mis à jour (symbole : {$data['currency_symbol']})");

        return back()->with('success', 'Paramètres de devise enregistrés.');
    }

    // ── Prix du point ────────────────────────────────────────────────────

    public function pointsSettings(): View
    {
        $pricePerUnit    = (float) SystemSetting::get('points.price_per_unit', 5.00);
        $annualEnabled   = (bool)  SystemSetting::get('billing.annual_enabled', true);
        $annualDiscount  = (int)   SystemSetting::get('billing.annual_discount_pct', 0);

        return view('admin.super_admin.settings.points', compact('pricePerUnit', 'annualEnabled', 'annualDiscount'));
    }

    public function updateBillingAnnual(Request $request): RedirectResponse
    {
        $request->validate([
            'billing_annual_discount_pct' => ['required', 'integer', 'min:0', 'max:80'],
        ]);

        $enabled = $request->boolean('billing_annual_enabled');

        SystemSetting::updateOrCreate(
            ['key' => 'billing.annual_enabled'],
            ['value' => $enabled ? '1' : '0', 'type' => 'bool', 'group' => 'billing']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'billing.annual_discount_pct'],
            ['value' => $request->billing_annual_discount_pct, 'type' => 'int', 'group' => 'billing']
        );
        Cache::forget('system_settings');

        ActivityLogger::log('admin.settings.updated', "Facturation annuelle : " . ($enabled ? 'activée' : 'désactivée') . ", remise {$request->billing_annual_discount_pct}%");

        return back()->with('success', 'Paramètres de facturation annuelle enregistrés.');
    }

    public function updatePointsPrice(Request $request): RedirectResponse
    {
        $request->validate([
            'points_price_per_unit' => ['required', 'numeric', 'min:0.01', 'max:9999'],
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'points.price_per_unit'],
            ['value' => $request->points_price_per_unit, 'type' => 'float', 'group' => 'points']
        );
        Cache::forget('system_settings');

        ActivityLogger::log('admin.settings.updated', "Prix du point mis à jour : {$request->points_price_per_unit}");

        return back()->with('success', 'Prix du point enregistré.');
    }

    // ── §5.2 Welcome popup ───────────────────────────────────────────────

    public function welcomePopup(): View
    {
        $settings  = SystemSetting::where('group', 'welcome_popup')->get()->keyBy('key');
        $interests = \App\Models\Interest::orderBy('name')->get();

        return view('admin.super_admin.settings.welcome-popup', compact('settings', 'interests'));
    }

    public function updateWelcomePopup(Request $request): RedirectResponse
    {
        $request->validate([
            'welcome_popup_enabled'   => ['boolean'],
            'welcome_popup_criteria'  => ['required', 'in:none,same_city,same_region,same_interest,both'],
            'welcome_popup_count'     => ['required', 'integer', 'in:2,3,4'],
            'welcome_popup_frequency' => ['required', 'in:once,session,always'],
            'welcome_popup_title'     => ['nullable', 'string', 'max:120'],
            'welcome_popup_subtitle'  => ['nullable', 'string', 'max:200'],
            'welcome_popup_btn_later' => ['nullable', 'string', 'max:60'],
            'welcome_popup_btn_cta'   => ['nullable', 'string', 'max:60'],
        ]);

        $fields = [
            'welcome_popup_enabled'   => $request->boolean('welcome_popup_enabled') ? '1' : '0',
            'welcome_popup_criteria'  => $request->welcome_popup_criteria,
            'welcome_popup_count'     => $request->welcome_popup_count,
            'welcome_popup_frequency' => $request->welcome_popup_frequency,
            'welcome_popup_title'     => $request->welcome_popup_title     ?? '',
            'welcome_popup_subtitle'  => $request->welcome_popup_subtitle  ?? '',
            'welcome_popup_btn_later' => $request->welcome_popup_btn_later ?? '',
            'welcome_popup_btn_cta'   => $request->welcome_popup_btn_cta   ?? '',
        ];

        foreach ($fields as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => 'welcome_popup',
                'type'  => $key === 'welcome_popup_enabled' ? 'bool' : ($key === 'welcome_popup_count' ? 'int' : 'string'),
            ]);
        }

        Cache::forget('system_settings');
        ActivityLogger::log('admin.settings.updated', "Paramètres popup de bienvenue mis à jour (critère : {$request->welcome_popup_criteria})");

        return back()->with('success', 'Paramètres du popup enregistrés.');
    }

    // ── §5.3 Page À propos ───────────────────────────────────────────────

    public function aboutPage(): View
    {
        $settings = SystemSetting::where('group', 'about_page')->get()->keyBy('key');
        return view('admin.super_admin.settings.about-page', compact('settings'));
    }

    public function updateAboutPage(Request $request): RedirectResponse
    {
        $request->validate([
            'about_enabled'       => ['boolean'],
            'about_title'         => ['required', 'string', 'max:100'],
            'about_tagline'       => ['nullable', 'string', 'max:200'],
            'about_mission'       => ['nullable', 'string', 'max:600'],
            'about_content'       => ['nullable', 'string', 'max:10000'],
            'about_contact_email' => ['nullable', 'email', 'max:150'],
            'about_founded_year'  => ['nullable', 'integer', 'min:2000', 'max:2030'],
            'about_cta_label'     => ['nullable', 'string', 'max:60'],
            'about_cta_url'       => ['nullable', 'string', 'max:255'],
        ]);

        $fields = [
            'about_enabled'       => $request->boolean('about_enabled') ? '1' : '0',
            'about_title'         => $request->about_title,
            'about_tagline'       => $request->about_tagline       ?? '',
            'about_mission'       => $request->about_mission       ?? '',
            'about_content'       => $request->about_content       ?? '',
            'about_contact_email' => $request->about_contact_email ?? '',
            'about_founded_year'  => $request->about_founded_year  ?? '',
            'about_cta_label'     => $request->about_cta_label     ?? '',
            'about_cta_url'       => $request->about_cta_url       ?? '',
        ];

        foreach ($fields as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => 'about_page',
                'type'  => $key === 'about_enabled' ? 'bool' : 'string',
            ]);
        }

        Cache::forget('system_settings');
        ActivityLogger::log('admin.settings.updated', 'Page À propos mise à jour');

        return back()->with('success', 'Page À propos enregistrée.');
    }

    // ── §5.x Payments ─────────────────────────────────────────────────────

    public function payments(): View
    {
        $settings = SystemSetting::where('group', 'payments')->get()->keyBy('key');

        // Bank transfer settings
        $bankTransferEnabled = (bool) SystemSetting::where('key', 'bank_transfer.enabled')->first()?->value;
        $bankTransferDetails = SystemSetting::where('key', 'bank_transfer.details')->first()?->value ?? '';

        return view('admin.super_admin.settings.payments', compact('settings', 'bankTransferEnabled', 'bankTransferDetails'));
    }

    public function updatePayments(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'point_price_cents'                  => ['required', 'integer', 'min:1', 'max:100000'],
            'subscription_test_mode'             => ['nullable', 'boolean'],
            'subscription_test_monthly_minutes'  => ['nullable', 'integer', 'min:1', 'max:10080'],
            'subscription_test_annual_minutes'   => ['nullable', 'integer', 'min:1', 'max:10080'],
            'bank_transfer_enabled'              => ['nullable', 'boolean'],
            'bank_transfer_details'              => ['nullable', 'string', 'max:2000'],
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'payments.point_price_cents'],
            ['value' => (string) $data['point_price_cents'], 'group' => 'payments', 'type' => 'int']
        );

        $testMode = !empty($data['subscription_test_mode']);
        SystemSetting::updateOrCreate(
            ['key' => 'payments.subscription_test_mode'],
            ['value' => $testMode ? '1' : '0', 'group' => 'payments', 'type' => 'bool']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'payments.subscription_test_monthly_minutes'],
            ['value' => (string) ($data['subscription_test_monthly_minutes'] ?? 5), 'group' => 'payments', 'type' => 'int']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'payments.subscription_test_annual_minutes'],
            ['value' => (string) ($data['subscription_test_annual_minutes'] ?? 10), 'group' => 'payments', 'type' => 'int']
        );

        // Bank transfer
        $bankEnabled = $request->boolean('bank_transfer_enabled');
        SystemSetting::updateOrCreate(
            ['key' => 'bank_transfer.enabled'],
            ['value' => $bankEnabled ? '1' : '0', 'group' => 'payments', 'type' => 'bool']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'bank_transfer.details'],
            ['value' => $data['bank_transfer_details'] ?? '', 'group' => 'payments', 'type' => 'string']
        );

        Cache::forget('system_settings');
        ActivityLogger::log('admin.settings.updated', "Paramètres de paiement mis à jour (virement=" . ($bankEnabled ? 'activé' : 'désactivé') . ")");

        return back()->with('success', 'Paramètres enregistrés.');
    }

    // ── Lancement officiel de la plateforme ─────────────────────────────────

    public function launch(): View
    {
        $launchedRow = SystemSetting::where('key', 'platform_launched')->first();
        $messageRow  = SystemSetting::where('key', 'prelaunch_message')->first();

        $launched = $launchedRow ? filter_var($launchedRow->value, FILTER_VALIDATE_BOOLEAN) : true;
        $message  = $messageRow?->value ?: 'Votre inscription est confirmée. Nous vous préviendrons par email dès l\'ouverture officielle de la plateforme.';

        $pendingCount = \App\Models\User::where('role', 'user')->count();

        return view('admin.super_admin.settings.launch', compact('launched', 'message', 'pendingCount'));
    }

    public function updateLaunch(Request $request): RedirectResponse
    {
        $request->validate([
            'platform_launched' => ['nullable', 'boolean'],
            'prelaunch_message' => ['required', 'string', 'max:300'],
        ]);

        $launched = $request->boolean('platform_launched');

        SystemSetting::updateOrCreate(['key' => 'platform_launched'], ['value' => $launched ? '1' : '0', 'group' => 'launch', 'type' => 'bool']);
        SystemSetting::updateOrCreate(['key' => 'prelaunch_message'], ['value' => $request->prelaunch_message, 'group' => 'launch', 'type' => 'string']);
        Cache::forget('system_settings');

        $status = $launched ? 'La plateforme est officiellement lancée — tous les inscrits ont maintenant accès complet.' : 'Mode pré-lancement réactivé — seule la page d\'inscription reste accessible.';
        ActivityLogger::log('admin.settings.updated', 'Lancement plateforme : ' . ($launched ? 'activé' : 'désactivé'));

        return back()->with('success', $status);
    }

    // ── §5.3 Maintenance ──────────────────────────────────────────────────

    public function maintenance(): View
    {
        $settings = SystemSetting::where('group', 'maintenance')->get()->keyBy('key');
        return view('admin.super_admin.settings.maintenance', compact('settings'));
    }

    public function updateMaintenance(Request $request): RedirectResponse
    {
        $request->validate([
            'maintenance_banner_enabled' => ['boolean'],
            'maintenance_banner_message' => ['required', 'string', 'max:300'],
        ]);

        SystemSetting::set('maintenance_banner_enabled', $request->boolean('maintenance_banner_enabled') ? '1' : '0');
        SystemSetting::set('maintenance_banner_message', $request->maintenance_banner_message);
        Cache::forget('system_settings');

        $status = $request->boolean('maintenance_banner_enabled') ? 'activé' : 'désactivé';
        ActivityLogger::log('admin.settings.updated', "Bandeau de maintenance {$status}");
        return back()->with('success', "Bandeau de maintenance {$status}.");
    }

    /* ─────────────────────────────── ADMIN MENU ─────────────────────────────── */

    public static function defaultAdminMenuPlateforme(): array
    {
        return [
            ['key' => 'dashboard',  'label' => 'Tableau de bord',  'visible' => true],
            ['key' => 'users',      'label' => 'Utilisateurs',      'visible' => true],
            ['key' => 'leads',      'label' => 'Leads',             'visible' => true],
            ['key' => 'notation',   'label' => 'Notation & Badges', 'visible' => true],
            ['key' => 'events',     'label' => 'Événements',        'visible' => true],
            ['key' => 'groups',     'label' => 'Groupes / Pôles',   'visible' => true],
            ['key' => 'videos',     'label' => 'Vidéos',            'visible' => true],
            ['key' => 'feedbacks',  'label' => 'Feedbacks',         'visible' => true],
        ];
    }

    public static function defaultAdminMenuSuperAdmin(): array
    {
        return [
            ['key' => 'analytics',        'label' => 'Analytiques',         'visible' => true],
            ['key' => 'subscribers',      'label' => 'Abonnés',             'visible' => true],
            ['key' => 'reports',          'label' => 'Signalements',        'visible' => true],
            ['key' => 'rgpd',             'label' => 'RGPD',                'visible' => true],
            ['key' => 'consuls',          'label' => 'Consuls',             'visible' => true],
            ['key' => 'ambassadors',      'label' => 'Ambassadeurs',        'visible' => true],
            ['key' => 'admins',           'label' => 'Admins',              'visible' => true],
            ['key' => 'plans',            'label' => 'Plans & Permissions', 'visible' => true],
            ['key' => 'regions',          'label' => 'Régions / Villes',    'visible' => true],
            ['key' => 'sectors',          'label' => 'Secteurs',            'visible' => true],
            ['key' => 'countries',        'label' => 'Pays',                'visible' => true],
            ['key' => 'interests',        'label' => 'Intérêts',            'visible' => true],
            ['key' => 'event_categories', 'label' => 'Catégories events',   'visible' => true],
            ['key' => 'activity_log',     'label' => "Journal d'activité",  'visible' => true],
            ['key' => 'settings',         'label' => 'Paramètres',          'visible' => true],
            ['key' => 'payments_cfg',     'label' => 'Paiements (config)',  'visible' => true],
            ['key' => 'menu_nav',         'label' => 'Menu navigation',     'visible' => true],
            ['key' => 'admin_menu_nav',   'label' => 'Menu administration', 'visible' => true],
            ['key' => 'welcome_popup',    'label' => 'Popup bienvenue',     'visible' => true],
            ['key' => 'maintenance',      'label' => 'Maintenance',         'visible' => true],
            ['key' => 'email_templates',  'label' => 'Templates email',     'visible' => true],
            ['key' => 'pages',            'label' => 'Pages légales',       'visible' => true],
            ['key' => 'about',            'label' => 'Page À propos',       'visible' => true],
            ['key' => 'smtp',             'label' => 'Email / SMTP',        'visible' => true],
            ['key' => 'geo_block',        'label' => 'Blocage géographique','visible' => true],
        ];
    }

    public function adminMenuSettings(): View
    {
        $platRaw = SystemSetting::get('admin_nav_plateforme', '');
        $saRaw   = SystemSetting::get('admin_nav_superadmin', '');

        $plat = $platRaw ? (json_decode($platRaw, true) ?? []) : self::defaultAdminMenuPlateforme();
        $sa   = $saRaw   ? (json_decode($saRaw,   true) ?? []) : self::defaultAdminMenuSuperAdmin();

        // Merge any newly-added default items
        foreach (self::defaultAdminMenuPlateforme() as $def) {
            if (! collect($plat)->pluck('key')->contains($def['key'])) $plat[] = $def;
        }
        foreach (self::defaultAdminMenuSuperAdmin() as $def) {
            if (! collect($sa)->pluck('key')->contains($def['key'])) $sa[] = $def;
        }

        return view('admin.super_admin.settings.admin-menu', compact('plat', 'sa'));
    }

    public function updateAdminMenuSettings(Request $request): RedirectResponse
    {
        $section  = $request->input('section', 'plateforme');
        $raw      = $request->input('config_json', '');
        $incoming = $raw ? json_decode($raw, true) : [];

        $defaults = collect(
            $section === 'superadmin'
                ? self::defaultAdminMenuSuperAdmin()
                : self::defaultAdminMenuPlateforme()
        )->keyBy('key');

        $config = [];
        foreach ((array) $incoming as $entry) {
            $key = $entry['key'] ?? '';
            if ($defaults->has($key)) {
                $config[] = [
                    'key'     => $key,
                    'label'   => $defaults[$key]['label'],
                    'visible' => (bool) ($entry['visible'] ?? true),
                ];
            }
        }

        if (empty($config)) {
            return back()->withErrors(['config_json' => 'Aucun élément reçu — sauvegarde annulée.']);
        }

        $settingKey = $section === 'superadmin' ? 'admin_nav_superadmin' : 'admin_nav_plateforme';
        SystemSetting::set($settingKey, json_encode($config));

        ActivityLogger::log('admin.settings.updated', "Menu admin ({$section}) mis à jour");
        return back()->with('success', 'Menu d\'administration mis à jour.');
    }

    /* ─────────────────────────────── USER MENU ──────────────────────────────── */

    /** Default ordered list of nav items — used when no custom config saved yet. */
    public static function defaultMenuItems(): array
    {
        return [
            ['key' => 'dashboard',  'label' => 'Start',        'visible' => true,  'fixed' => false],
            ['key' => 'members',    'label' => 'Membres',       'visible' => true,  'fixed' => false],
            ['key' => 'network',    'label' => 'Réseau',        'visible' => true,  'fixed' => false],
            ['key' => 'groups',     'label' => 'Groupes',       'visible' => true,  'fixed' => false],
            ['key' => 'events',     'label' => 'Événements',    'visible' => true,  'fixed' => false],
            ['key' => 'leads',      'label' => 'Leads',         'visible' => true,  'fixed' => false],
            ['key' => 'chat',       'label' => 'Chat',          'visible' => true,  'fixed' => false],
        ];
    }

    public function menuSettings(): View
    {
        $row   = SystemSetting::where('key', 'nav_menu_config')->first();
        $saved = $row ? $row->value : '';
        $items = $saved ? (json_decode($saved, true) ?? []) : self::defaultMenuItems();

        // Ensure all default items are represented (in case new ones were added)
        $defaults = collect(self::defaultMenuItems())->keyBy('key');
        $itemKeys  = collect($items)->pluck('key')->all();
        foreach ($defaults as $key => $def) {
            if (! in_array($key, $itemKeys)) {
                $items[] = $def;
            }
        }

        return view('admin.super_admin.settings.menu', compact('items'));
    }

    public function updateMenuSettings(Request $request)
    {
        // New approach: order[] contains keys in display order, visible[] contains checked keys
        $order   = $request->input('order', []);
        $visible = $request->input('visible', []);   // only checked checkboxes are submitted

        $defaults = collect(self::defaultMenuItems())->keyBy('key');
        $config   = [];

        foreach ($order as $key) {
            if ($defaults->has($key)) {
                $config[] = [
                    'key'     => $key,
                    'label'   => $defaults[$key]['label'],
                    'visible' => in_array($key, (array) $visible),
                    'fixed'   => false,
                ];
            }
        }

        if (empty($config)) {
            return back()->withErrors(['order' => 'Aucun élément reçu — sauvegarde annulée.']);
        }

        SystemSetting::set('nav_menu_config', json_encode($config));
        Cache::forget('system_settings');

        ActivityLogger::log('admin.settings.updated', 'Configuration du menu de navigation mise à jour');

        return redirect()->route('admin.super.settings.menu')->with('success', 'Menu de navigation mis à jour.');
    }

    /* ─────────────────────────────── GEO BLOCK ─────────────────────────────── */

    public static function allCountries(): array
    {
        return [
            'AF'=>'Afghanistan','ZA'=>'Afrique du Sud','AL'=>'Albanie','DZ'=>'Algérie','DE'=>'Allemagne',
            'AD'=>'Andorre','AO'=>'Angola','SA'=>'Arabie saoudite','AR'=>'Argentine','AM'=>'Arménie',
            'AU'=>'Australie','AT'=>'Autriche','AZ'=>'Azerbaïdjan','BS'=>'Bahamas','BH'=>'Bahreïn',
            'BD'=>'Bangladesh','BY'=>'Biélorussie','BE'=>'Belgique','BZ'=>'Belize','BJ'=>'Bénin',
            'BO'=>'Bolivie','BA'=>'Bosnie-Herzégovine','BW'=>'Botswana','BR'=>'Brésil','BN'=>'Brunei',
            'BG'=>'Bulgarie','BF'=>'Burkina Faso','BI'=>'Burundi','KH'=>'Cambodge','CM'=>'Cameroun',
            'CA'=>'Canada','CV'=>'Cap-Vert','CL'=>'Chili','CN'=>'Chine','CY'=>'Chypre',
            'CO'=>'Colombie','KM'=>'Comores','CG'=>'Congo','KR'=>'Corée du Sud','KP'=>'Corée du Nord',
            'HR'=>'Croatie','CU'=>'Cuba','DK'=>'Danemark','DJ'=>'Djibouti','EG'=>'Égypte',
            'AE'=>'Émirats arabes unis','EC'=>'Équateur','ER'=>'Érythrée','ES'=>'Espagne','EE'=>'Estonie',
            'ET'=>'Éthiopie','FI'=>'Finlande','FR'=>'France','GA'=>'Gabon','GM'=>'Gambie',
            'GE'=>'Géorgie','GH'=>'Ghana','GR'=>'Grèce','GT'=>'Guatemala','GN'=>'Guinée',
            'HT'=>'Haïti','HN'=>'Honduras','HU'=>'Hongrie','IN'=>'Inde','ID'=>'Indonésie',
            'IQ'=>'Irak','IR'=>'Iran','IE'=>'Irlande','IS'=>'Islande','IL'=>'Israël',
            'IT'=>'Italie','JM'=>'Jamaïque','JP'=>'Japon','JO'=>'Jordanie','KZ'=>'Kazakhstan',
            'KE'=>'Kenya','KW'=>'Koweït','LB'=>'Liban','LY'=>'Libye','LI'=>'Liechtenstein',
            'LT'=>'Lituanie','LU'=>'Luxembourg','MK'=>'Macédoine du Nord','MG'=>'Madagascar','MY'=>'Malaisie',
            'MW'=>'Malawi','MV'=>'Maldives','ML'=>'Mali','MT'=>'Malte','MA'=>'Maroc',
            'MR'=>'Mauritanie','MU'=>'Maurice','MX'=>'Mexique','MD'=>'Moldavie','MC'=>'Monaco',
            'MN'=>'Mongolie','ME'=>'Monténégro','MZ'=>'Mozambique','MM'=>'Myanmar','NA'=>'Namibie',
            'NP'=>'Népal','NI'=>'Nicaragua','NE'=>'Niger','NG'=>'Nigéria','NO'=>'Norvège',
            'NZ'=>'Nouvelle-Zélande','OM'=>'Oman','UG'=>'Ouganda','UZ'=>'Ouzbékistan','PK'=>'Pakistan',
            'PA'=>'Panama','PY'=>'Paraguay','NL'=>'Pays-Bas','PE'=>'Pérou','PH'=>'Philippines',
            'PL'=>'Pologne','PT'=>'Portugal','QA'=>'Qatar','RO'=>'Roumanie','GB'=>'Royaume-Uni',
            'RU'=>'Russie','RW'=>'Rwanda','SN'=>'Sénégal','RS'=>'Serbie','SL'=>'Sierra Leone',
            'SG'=>'Singapour','SK'=>'Slovaquie','SI'=>'Slovénie','SO'=>'Somalie','SD'=>'Soudan',
            'SS'=>'Soudan du Sud','LK'=>'Sri Lanka','SE'=>'Suède','CH'=>'Suisse','SR'=>'Suriname',
            'SY'=>'Syrie','TJ'=>'Tadjikistan','TZ'=>'Tanzanie','TD'=>'Tchad','CZ'=>'Tchéquie',
            'TH'=>'Thaïlande','TG'=>'Togo','TT'=>'Trinité-et-Tobago','TN'=>'Tunisie','TM'=>'Turkménistan',
            'TR'=>'Turquie','UA'=>'Ukraine','UY'=>'Uruguay','US'=>'États-Unis','VE'=>'Venezuela',
            'VN'=>'Viêt Nam','YE'=>'Yémen','ZM'=>'Zambie','ZW'=>'Zimbabwe',
        ];
    }

    public function geoBlockSettings(): View
    {
        // Lecture directe DB (bypass cache) pour avoir l'état le plus récent
        $rowCountries = SystemSetting::where('key', 'geo_blocked_countries')->first();
        $rowCities    = SystemSetting::where('key', 'geo_blocked_cities')->first();

        $blockedCountries = $rowCountries ? (json_decode($rowCountries->value, true) ?? []) : [];
        $blockedCities    = $rowCities    ? (json_decode($rowCities->value,    true) ?? []) : [];

        $countries = self::allCountries();
        $cities    = \App\Models\City::orderBy('name')->get(['id', 'name']);

        return view('admin.super_admin.settings.geo-block', compact(
            'blockedCountries', 'blockedCities', 'countries', 'cities'
        ));
    }

    public function updateGeoBlockSettings(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'countries');

        if ($type === 'regions') {
            // Checkboxes name="regions[]" — liste des régions cochées
            $blocked = array_values(array_filter($request->input('regions', [])));
            SystemSetting::set('geo_blocked_cities', json_encode($blocked));
            $msg = count($blocked) . ' région(s) bloquée(s).';
            ActivityLogger::log('admin.settings.updated', $msg);
        } else {
            // Checkboxes name="countries[]" — liste des codes pays cochés
            $blocked = array_values(array_filter($request->input('countries', [])));
            SystemSetting::set('geo_blocked_countries', json_encode($blocked));
            $msg = count($blocked) . ' pays bloqué(s).';
            ActivityLogger::log('admin.settings.updated', $msg);
        }

        Cache::forget('system_settings');
        return redirect()->route('admin.super.settings.geo-block')->with('success', 'Blocage géographique mis à jour — ' . $msg);
    }
}
