<?php

namespace Tests\Unit\Services\Notifications;

use App\Models\Condominium;
use App\Models\User;
use App\Notifications\SmokeTestNotification;
use App\Services\Notifications\DedupeGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DedupeGuardTest extends TestCase
{
    use RefreshDatabase;

    private DedupeGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guard = app(DedupeGuard::class);
    }

    public function test_notify_if_not_duplicate_creates_first_notification(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);
        $user->condominiums()->sync([$condominium->id]);

        $notification = new SmokeTestNotification(
            condominiumId: $condominium->id,
            category: 'system',
            title: 'Primeira',
            body: 'Corpo',
            actionUrl: '/dashboard',
            dedupeKey: 'dedupe-1',
            actorId: $user->id,
        );

        $sent = $this->guard->notifyIfNotDuplicate($user, $notification);

        $this->assertTrue($sent);
        $this->assertSame(1, $user->notifications()->count());
    }

    public function test_notify_if_not_duplicate_skips_existing_dedupe_key(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);
        $user->condominiums()->sync([$condominium->id]);

        $first = new SmokeTestNotification(
            condominiumId: $condominium->id,
            category: 'system',
            title: 'Primeira',
            body: 'Corpo',
            actionUrl: '/dashboard',
            dedupeKey: 'dedupe-repeat',
            actorId: $user->id,
        );

        $second = new SmokeTestNotification(
            condominiumId: $condominium->id,
            category: 'system',
            title: 'Segunda',
            body: 'Corpo 2',
            actionUrl: '/dashboard',
            dedupeKey: 'dedupe-repeat',
            actorId: $user->id,
        );

        $this->guard->notifyIfNotDuplicate($user, $first);
        $sent = $this->guard->notifyIfNotDuplicate($user, $second);

        $this->assertFalse($sent);
        $this->assertSame(1, $user->notifications()->count());
    }

    public function test_exists_detects_dedupe_key_for_notifiable(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);
        $user->condominiums()->sync([$condominium->id]);

        $user->notify(new SmokeTestNotification(
            condominiumId: $condominium->id,
            category: 'system',
            title: 'Existente',
            body: 'Corpo',
            actionUrl: '/dashboard',
            dedupeKey: 'exists-key',
            actorId: $user->id,
        ));

        $this->assertTrue($this->guard->exists($user, 'exists-key'));
        $this->assertFalse($this->guard->exists($user, 'other-key'));
    }
}
