<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\SmokeTestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
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
            'notification_email' => false,
            'notification_push' => false,
            'notification_sms' => false,
        ]);
        $this->userA->condominiums()->sync([$this->condominiumA->id, $this->condominiumB->id]);

        $this->userB = User::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'profile_id' => $this->profile->id,
        ]);
        $this->userB->condominiums()->sync([$this->condominiumB->id]);
    }

    public function test_database_notification_is_delivered_even_with_prefs_disabled(): void
    {
        $this->userA->notify(new SmokeTestNotification(
            condominiumId: $this->condominiumA->id,
            category: 'system',
            title: 'Teste prefs',
            body: 'Inbox always-on',
            actionUrl: '/dashboard',
            dedupeKey: 'prefs-test-'.$this->userA->id,
            actorId: $this->userA->id,
        ));

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $this->userA->id,
        ]);
    }

    public function test_can_list_notifications_for_active_tenant(): void
    {
        $this->seedNotification($this->userA, $this->condominiumA->id, 'alpha-1', 'Alpha 1');
        $this->seedNotification($this->userA, $this->condominiumB->id, 'beta-1', 'Beta 1');

        $response = $this->actingAsTenant($this->userA, $this->condominiumA)
            ->getJson('/api/notifications');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'Alpha 1');
    }

    public function test_unread_count_is_scoped_to_tenant(): void
    {
        $this->seedNotification($this->userA, $this->condominiumA->id, 'alpha-unread', 'Alpha unread');
        $this->seedNotification($this->userA, $this->condominiumB->id, 'beta-unread', 'Beta unread');

        $response = $this->actingAsTenant($this->userA, $this->condominiumA)
            ->getJson('/api/notifications/unread-count');

        $response->assertOk();
        $response->assertJson(['count' => 1]);
    }

    public function test_can_filter_unread_notifications(): void
    {
        $readNotification = $this->seedNotification(
            $this->userA,
            $this->condominiumA->id,
            'read-item',
            'Lida'
        );
        $readNotification->markAsRead();

        $this->seedNotification($this->userA, $this->condominiumA->id, 'unread-item', 'Não lida');

        $response = $this->actingAsTenant($this->userA, $this->condominiumA)
            ->getJson('/api/notifications?unread=1');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'Não lida');
    }

    public function test_can_mark_single_notification_as_read(): void
    {
        $notification = $this->seedNotification(
            $this->userA,
            $this->condominiumA->id,
            'mark-one',
            'Marcar uma'
        );

        $response = $this->actingAsTenant($this->userA, $this->condominiumA)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_read_returns_404_for_cross_tenant_notification(): void
    {
        $notification = $this->seedNotification(
            $this->userA,
            $this->condominiumB->id,
            'beta-only',
            'Somente Beta'
        );

        $response = $this->actingAsTenant($this->userA, $this->condominiumA)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertNotFound();
    }

    public function test_mark_read_returns_404_for_other_user_notification(): void
    {
        $notification = $this->seedNotification(
            $this->userB,
            $this->condominiumB->id,
            'user-b',
            'User B'
        );

        $response = $this->actingAsTenant($this->userA, $this->condominiumA)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertNotFound();
    }

    public function test_can_mark_all_notifications_as_read_for_tenant(): void
    {
        $this->seedNotification($this->userA, $this->condominiumA->id, 'all-1', 'All 1');
        $this->seedNotification($this->userA, $this->condominiumA->id, 'all-2', 'All 2');
        $this->seedNotification($this->userA, $this->condominiumB->id, 'beta-stays', 'Beta stays');

        $response = $this->actingAsTenant($this->userA, $this->condominiumA)
            ->postJson('/api/notifications/read-all');

        $response->assertOk();

        $this->assertSame(0, $this->userA->unreadNotifications()
            ->where('data->condominium_id', $this->condominiumA->id)
            ->count());

        $this->assertSame(1, $this->userA->unreadNotifications()
            ->where('data->condominium_id', $this->condominiumB->id)
            ->count());
    }

    public function test_denies_access_without_tenant_membership(): void
    {
        $outsider = User::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'profile_id' => $this->profile->id,
        ]);
        $outsider->condominiums()->sync([$this->condominiumB->id]);

        $response = $this->actingAs($outsider, 'api')
            ->withTenantHeader($this->condominiumA)
            ->getJson('/api/notifications');

        $response->assertForbidden();
    }

    private function seedNotification(User $user, int $condominiumId, string $dedupeKey, string $title)
    {
        $user->notify(new SmokeTestNotification(
            condominiumId: $condominiumId,
            category: 'system',
            title: $title,
            body: "Corpo de {$title}",
            actionUrl: '/dashboard',
            dedupeKey: $dedupeKey,
            actorId: $user->id,
        ));

        return $user->notifications()->latest()->first();
    }
}
