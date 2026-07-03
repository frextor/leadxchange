<?php

namespace Database\Seeders;

use App\Http\Controllers\Admin\SuperAdmin\PlanController;
use App\Models\PermissionDefinition;
use Illuminate\Database\Seeder;

class PermissionDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;
        foreach (PlanController::PERMISSIONS as $category => $perms) {
            foreach ($perms as $key => $def) {
                PermissionDefinition::updateOrCreate(
                    ['key' => $key],
                    [
                        'label'      => $def['label'],
                        'category'   => $category,
                        'type'       => $def['type'],
                        'null_label' => $def['null_label'] ?? null,
                        'sort_order' => ++$sort,
                    ]
                );
            }
        }
    }
}
