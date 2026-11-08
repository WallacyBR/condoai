<?php

namespace Tests\Feature;

use App\Enums\OccurrencePriority;
use App\Enums\OccurrenceStatus;
use App\Models\Condominium;
use App\Models\Module;
use App\Models\Occurrence;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class OccurrenceControllerTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    public function test_can_show_occurrence_with_profile_module_permission(): void
    {
        $condominium = Condominium::factory()->create();
        $profile = Profile::factory()->create(['name' => 'Zelador']);
        $module = Module::factory()->create(['name' => 'Ocorrências']);

        $profile->modules()->attach($module->id, [
            'can_view' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'scope' => 'condominium',
        ]);

        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $profile->id,
        ]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $user->id,
            'status' => OccurrenceStatus::OPEN,
            'priority' => OccurrencePriority::HIGH,
            'title' => 'Portão com falha',
        ]);

        $this->actingAsTenant($user, $condominium);

        $this->getJson("/api/occurrences/{$occurrence->id}")
            ->assertOk()
            ->assertJsonFragment([
                'id' => $occurrence->id,
                'title' => 'Portão com falha',
            ]);
    }
}
