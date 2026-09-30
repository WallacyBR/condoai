<?php

namespace Tests\Concerns;

use App\Models\Profile;
use Database\Seeders\DefaultProfileSeeder;
use Database\Seeders\ModuleSeeder;

trait SeedsDefaultPermissionMatrix
{
    protected function seedDefaultPermissionMatrix(): void
    {
        $this->seed(ModuleSeeder::class);
        $this->seed(DefaultProfileSeeder::class);
    }

    protected function defaultProfile(string $name): Profile
    {
        return Profile::query()->where('name', $name)->firstOrFail();
    }
}
