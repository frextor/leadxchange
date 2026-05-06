<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            ['name' => 'Technologie',       'icon' => '💻'],
            ['name' => 'Marketing',          'icon' => '📣'],
            ['name' => 'Finance',            'icon' => '💰'],
            ['name' => 'Ventes',             'icon' => '📈'],
            ['name' => 'Ressources humaines','icon' => '👥'],
            ['name' => 'Juridique',          'icon' => '⚖️'],
            ['name' => 'Santé',              'icon' => '🏥'],
            ['name' => 'Éducation',          'icon' => '🎓'],
            ['name' => 'Immobilier',         'icon' => '🏠'],
            ['name' => 'Consulting',         'icon' => '🤝'],
            ['name' => 'E-commerce',         'icon' => '🛍️'],
            ['name' => 'Startup',            'icon' => '🚀'],
            ['name' => 'Logistique',         'icon' => '🚚'],
            ['name' => 'Industrie',          'icon' => '🏭'],
            ['name' => 'Médias',             'icon' => '📺'],
            ['name' => 'Design',             'icon' => '🎨'],
            ['name' => 'Construction',       'icon' => '🏗️'],
            ['name' => 'Agriculture',        'icon' => '🌾'],
            ['name' => 'Tourisme',           'icon' => '✈️'],
            ['name' => 'Intelligence artificielle', 'icon' => '🤖'],
        ];

        foreach ($interests as $interest) {
            Interest::firstOrCreate(['name' => $interest['name']], ['icon' => $interest['icon']]);
        }
    }
}
