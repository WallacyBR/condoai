<?php

namespace Tests\Feature\Notifications;

use App\Events\Notifications\InboxSmokeRequested;
use App\Listeners\Notifications\SendInboxSmokeNotification;
use App\Models\Condominium;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class InboxSmokeQueueTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;

    protected Condominium $condominiumA;

    protected Condominium $condominiumB;

    protected Profile $profile;

    protected User $userA;

    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominiumA = Condominium::factory()->create(['name' => 'Condomínio Alpha']);
        $this->condominiumB = Condominium::factory()->create(['name' => 'Condomínio Beta']);
        $this->profile = Profile::factory()->create(['name' => 'Síndico']);

        $this->userA = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->profile->id,
        ]);
        $this->userA->condominiums()->sync([$this->condominiumA->id, $this->condominiumB->id]);

        $this->userB = User::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'profile_id' => $this->profile->id,
        ]);
        $this->userB->condominiums()->sync([$this->condominiumB->id]);
    }

    public function test_inbox_smoke_event_creates_notification_with_sync_driver(): void
    {
        event(new InboxSmokeRequested(
            condominiumId: $this->condominiumA->id,
            actorId: $this->userA->id,
            userId: $this->userA->id,
        ));

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $this->userA->id,
        ]);

        $notification = $this->userA->notifications()->first();
        $this->assertSame($this->condominiumA->id, $notification->data['condominium_id']);
        $this->assertSame('Smoke E2', $notification->data['title']);
    }

    public function test_listener_is_queued_when_using_queue_fake(): void
    {
        Queue::fake();

        event(new InboxSmokeRequested(
            condominiumId: $this->condominiumA->id,
            actorId: $this->userA->id,
            userId: $this->userA->id,
        ));

        Queue::assertPushed(CallQueuedListener::class, function (CallQueuedListener $job): bool {
            return $job->class === SendInboxSmokeNotification::class;
        });
    }

    public function test_listener_fails_closed_for_invalid_condominium(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $listener = app(SendInboxSmokeNotification::class);
        $listener->handle(new InboxSmokeRequested(
            condominiumId: 999999,
            actorId: null,
            userId: $this->userA->id,
        ));
    }

    public function test_listener_fails_closed_when_user_not_in_condominium(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('não pertence ao condomínio');

        $listener = app(SendInboxSmokeNotification::class);
        $listener->handle(new InboxSmokeRequested(
            condominiumId: $this->condominiumA->id,
            actorId: null,
            userId: $this->userB->id,
        ));
    }

    public function test_notification_from_condo_a_does_not_appear_in_condo_b_inbox(): void
    {
        event(new InboxSmokeRequested(
            condominiumId: $this->condominiumA->id,
            actorId: $this->userA->id,
            userId: $this->userA->id,
        ));

        $response = $this->actingAsTenant($this->userA, $this->condominiumB)
            ->getJson('/api/notifications');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_notification_appears_only_in_matching_tenant_inbox(): void
    {
        event(new InboxSmokeRequested(
            condominiumId: $this->condominiumA->id,
            actorId: $this->userA->id,
            userId: $this->userA->id,
        ));

        $responseAlpha = $this->actingAsTenant($this->userA, $this->condominiumA)
            ->getJson('/api/notifications');

        $responseAlpha->assertOk();
        $responseAlpha->assertJsonCount(1, 'data');
        $responseAlpha->assertJsonPath('data.0.title', 'Smoke E2');
    }

    public function test_event_does_not_create_cross_tenant_row_for_outsider(): void
    {
        $outsider = User::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'profile_id' => $this->profile->id,
        ]);
        $outsider->condominiums()->sync([$this->condominiumB->id]);

        Event::fake([InboxSmokeRequested::class]);

        event(new InboxSmokeRequested(
            condominiumId: $this->condominiumA->id,
            actorId: $this->userA->id,
            userId: $outsider->id,
        ));

        Event::assertDispatched(InboxSmokeRequested::class);

        $listener = app(SendInboxSmokeNotification::class);

        try {
            $listener->handle(new InboxSmokeRequested(
                condominiumId: $this->condominiumA->id,
                actorId: null,
                userId: $outsider->id,
            ));
        } catch (\RuntimeException) {
            // fail-closed esperado
        }

        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $outsider->id,
        ]);
    }
}
