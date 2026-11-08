<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Notice;
use App\Models\Tower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): string
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);
        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);
        return $login['authorization']['token'];
    }

    public function test_authenticated_user_can_create_notice(): void
    {
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $tower = Tower::factory()->create(['condominium_id' => $condo->id]);

        $data = [
            'title' => 'Novo aviso',
            'body' => 'Conteúdo do aviso',
            'condominium_id' => $condo->id,
            'targets' => [
                ['type' => 'tower', 'id' => $tower->id]
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/notices', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Novo aviso']);

        $this->assertDatabaseHas('notice_targets', [
            'target_type' => Tower::class,
            'target_id' => $tower->id
        ]);
    }

    public function test_filter_notices_by_target(): void
    {
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $towerA = Tower::factory()->create(['condominium_id' => $condo->id]);
        $towerB = Tower::factory()->create(['condominium_id' => $condo->id]);

        $noticeA = Notice::factory()->create(['condominium_id' => $condo->id]);
        $noticeA->noticeTargets()->create(['target_type' => Tower::class, 'target_id' => $towerA->id]);

        $noticeB = Notice::factory()->create(['condominium_id' => $condo->id]);
        $noticeB->noticeTargets()->create(['target_type' => Tower::class, 'target_id' => $towerB->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/notices?condominium_id={$condo->id}&target_type=tower&target_id={$towerA->id}");

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['id' => $noticeA->id]);
    }
}
