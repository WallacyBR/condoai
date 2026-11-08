<?php

namespace Tests\Unit;

use App\Enums\ModuleName;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AssignsModulePermissions;
use Tests\TestCase;

class UserModulePermissionsTest extends TestCase
{
    use AssignsModulePermissions;
    use RefreshDatabase;

    public function test_can_module_returns_false_when_profile_has_no_module(): void
    {
        $profile = Profile::factory()->create(['name' => 'Teste']);
        $user = User::factory()->create(['profile_id' => $profile->id]);

        $this->assertFalse($user->canModule(ModuleName::Ocorrencias->value, 'view'));
    }

    public function test_can_module_respects_pivot_flags(): void
    {
        $profile = Profile::factory()->create(['name' => 'Teste']);
        $this->assignModulePermissions($profile, ModuleName::Financeiro->value, [
            'can_view' => true,
            'can_create' => true,
            'can_update' => false,
            'can_delete' => false,
            'scope' => 'condominium',
        ]);

        $user = User::factory()->create(['profile_id' => $profile->id]);

        $this->assertTrue($user->canModule(ModuleName::Financeiro->value, 'view'));
        $this->assertTrue($user->canModule(ModuleName::Financeiro->value, 'create'));
        $this->assertFalse($user->canModule(ModuleName::Financeiro->value, 'update'));
        $this->assertFalse($user->canModule(ModuleName::Financeiro->value, 'delete'));
    }

    public function test_sysop_bypasses_module_checks(): void
    {
        $profile = Profile::factory()->create(['name' => 'SysOp']);
        $user = User::factory()->create(['profile_id' => $profile->id]);

        $this->assertTrue($user->canModule(ModuleName::Financeiro->value, 'delete'));
        $this->assertSame('all', $user->moduleScope(ModuleName::Financeiro->value));
    }

    public function test_module_scope_returns_pivot_scope(): void
    {
        $profile = Profile::factory()->create(['name' => 'Teste']);
        $this->assignModulePermissions($profile, ModuleName::Ocorrencias->value, [
            'can_view' => true,
            'scope' => 'own',
        ]);

        $user = User::factory()->create(['profile_id' => $profile->id]);

        $this->assertSame('own', $user->moduleScope(ModuleName::Ocorrencias->value));
    }
}
