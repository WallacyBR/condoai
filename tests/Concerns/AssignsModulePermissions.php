<?php

namespace Tests\Concerns;

use App\Models\Module;
use App\Models\Profile;

trait AssignsModulePermissions
{
    protected function assignModulePermissions(
        Profile $profile,
        string $moduleName,
        array $permissions
    ): Module {
        $module = Module::query()->firstOrCreate(
            ['name' => $moduleName],
            ['description' => "Módulo {$moduleName}"]
        );

        $profile->modules()->syncWithoutDetaching([
            $module->id => array_merge([
                'can_view' => false,
                'can_create' => false,
                'can_update' => false,
                'can_delete' => false,
                'scope' => 'condominium',
            ], $permissions),
        ]);

        return $module;
    }
}
