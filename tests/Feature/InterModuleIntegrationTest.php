<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\OccurrencePriority;
use App\Enums\OccurrenceStatus;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\ChartAccount;
use App\Models\CommonArea;
use App\Models\CommonAreaBooking;
use App\Models\Condominium;
use App\Models\MaintenanceSchedule;
use App\Models\Occurrence;
use App\Models\Package;
use App\Models\Profile;
use App\Models\Receivable;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class InterModuleIntegrationTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    private Condominium $condominium;

    private User $user;

    private Apartment $apartment;

    private Resident $resident;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();
        $profile = Profile::factory()->create(['name' => 'Síndico']);
        $this->user = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $profile->id,
        ]);

        $block = Block::factory()->create(['condominium_id' => $this->condominium->id]);
        $this->apartment = Apartment::factory()->create([
            'condominium_id' => $this->condominium->id,
            'block_id' => $block->id,
            'number' => '101',
            'floor' => 1,
            'owner_id' => null,
            'tenant_id' => null,
        ]);

        $this->resident = Resident::create([
            'condominium_id' => $this->condominium->id,
            'user_id' => $this->user->id,
            'name' => 'Morador Teste',
            'email' => 'morador@teste.com',
            'phone' => '11999999999',
            'apartment_id' => $this->apartment->id,
        ]);

        $this->apartment->update(['owner_id' => $this->resident->id]);

        $this->actingAsTenant($this->user, $this->condominium);
    }

    public function test_booking_common_area_creates_receivable_when_confirmed_and_paid(): void
    {
        $this->withoutExceptionHandling();
        $commonArea = CommonArea::create([
            'condominium_id' => $this->condominium->id,
            'name' => 'Salão Gourmet',
            'capacity' => 30,
            'active' => true,
            'price_per_hour' => 50.00,
            'min_booking_hours' => 1,
            'max_booking_hours' => 8,
            'requires_approval' => false,
            'allow_custom_periods' => true,
            'booking_schedule' => array_fill(0, 7, ['type' => 'hourly', 'enabled' => true]),
            'availability_schedule' => array_fill(0, 7, ['enabled' => true, 'start_time' => '08:00', 'end_time' => '23:59']),
        ]);

        $bookingData = [
            'apartment_id' => $this->apartment->id,
            'booking_type' => 'custom',
            'start_time' => now()->addDays(2)->setTime(18, 0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->setTime(22, 0)->format('Y-m-d H:i:s'), // 4 horas * 50 = 200
        ];

        $response = $this->postJson("/api/common-areas/{$commonArea->id}/bookings", $bookingData);
        $response->assertStatus(201);

        $booking = CommonAreaBooking::where('common_area_id', $commonArea->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals('confirmed', $booking->status);
        $this->assertNotNull($booking->receivable_id);

        $receivable = Receivable::find($booking->receivable_id);
        $this->assertNotNull($receivable);
        $this->assertEquals(200.00, (float) $receivable->amount);
        $this->assertEquals($this->condominium->id, $receivable->condominium_id);
        $this->assertEquals('pendente', $receivable->status);
        $this->assertEquals($booking->id, $receivable->booking_id);
    }

    public function test_cancelling_booking_cancels_associated_pending_receivable(): void
    {
        $commonArea = CommonArea::create([
            'condominium_id' => $this->condominium->id,
            'name' => 'Churrasqueira',
            'capacity' => 20,
            'active' => true,
            'price_per_hour' => 30.00,
            'min_booking_hours' => 1,
            'max_booking_hours' => 5,
            'requires_approval' => false,
            'allow_custom_periods' => true,
        ]);

        $booking = CommonAreaBooking::create([
            'condominium_id' => $this->condominium->id,
            'common_area_id' => $commonArea->id,
            'apartment_id' => $this->apartment->id,
            'resident_id' => $this->resident->id,
            'booking_date' => now()->addDays(3)->toDateString(),
            'start_time' => now()->addDays(3)->setTime(12, 0),
            'end_time' => now()->addDays(3)->setTime(16, 0),
            'duration_hours' => 4,
            'booking_type' => 'custom',
            'status' => 'confirmed',
            'total_price' => 120.00,
        ]);

        $chartAccount = ChartAccount::create([
            'condominium_id' => $this->condominium->id,
            'name' => 'Receitas Áreas',
            'account_number' => '1.01',
            'type' => AccountType::RECEITA->value,
        ]);

        $receivable = Receivable::create([
            'condominium_id' => $this->condominium->id,
            'chart_account_id' => $chartAccount->id,
            'due_date' => now()->addDays(3)->toDateString(),
            'amount' => 120.00,
            'status' => 'pendente',
            'booking_id' => $booking->id,
        ]);

        $booking->update(['receivable_id' => $receivable->id]);

        $response = $this->patchJson("/api/common-areas/{$commonArea->id}/bookings/{$booking->id}/cancel", [
            'reason' => 'Imprevisto pessoal',
        ]);

        $response->assertOk();
        $this->assertEquals('cancelled', $booking->fresh()->status);
        $this->assertEquals('cancelado', $receivable->fresh()->status);
    }

    public function test_converting_occurrence_to_maintenance(): void
    {
        $occurrence = Occurrence::create([
            'condominium_id' => $this->condominium->id,
            'user_id' => $this->user->id,
            'title' => 'Infiltração na garagem subsolo',
            'description' => 'Goteira intensa próxima à vaga 12',
            'status' => OccurrenceStatus::OPEN,
            'priority' => OccurrencePriority::HIGH,
        ]);

        $convertData = [
            'type' => 'corretiva',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'notes' => 'Acionar encanador credenciado',
        ];

        $response = $this->postJson("/api/occurrences/{$occurrence->id}/convert-to-maintenance", $convertData);
        $response->assertStatus(201);

        $occurrence->refresh();
        $this->assertEquals(OccurrenceStatus::IN_PROGRESS, $occurrence->status);
        $this->assertNotNull($occurrence->converted_to_maintenance_id);

        $schedule = MaintenanceSchedule::find($occurrence->converted_to_maintenance_id);
        $this->assertNotNull($schedule);
        $this->assertEquals($occurrence->id, $schedule->occurrence_id);
        $this->assertStringContainsString('Infiltração na garagem subsolo', $schedule->title);
    }

    public function test_maintenance_record_creates_expense_request_when_cost_informed(): void
    {
        $schedule = MaintenanceSchedule::create([
            'condominium_id' => $this->condominium->id,
            'title' => 'Reparo no gerador',
            'type' => 'corretiva',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(1)->toDateString(),
        ]);

        $recordData = [
            'maintenance_schedule_id' => $schedule->id,
            'performed_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Troca de filtro de óleo e correia',
            'cost' => 450.00,
            'invoice_number' => 'NF-9872',
            'create_expense_request' => true,
        ];

        $response = $this->postJson('/api/maintenance/records', $recordData);
        $response->assertStatus(201);

        $recordId = $response->json('id');
        $expenseRequestId = $response->json('expense_request_id');

        $this->assertNotNull($expenseRequestId);
        $this->assertDatabaseHas('expense_requests', [
            'id' => $expenseRequestId,
            'amount' => 450.00,
            'condominium_id' => $this->condominium->id,
        ]);
    }

    public function test_public_visitor_invite_token_and_gatehouse_lookup(): void
    {
        $visitor = \App\Models\Visitor::create([
            'condominium_id' => $this->condominium->id,
            'name' => 'Carlos Convidado',
            'document' => '12345678900',
        ]);

        $accessRequest = \App\Models\AccessRequest::create([
            'condominium_id' => $this->condominium->id,
            'visitor_id' => $visitor->id,
            'resident_id' => $this->resident->id,
            'apartment_id' => $this->apartment->id,
            'visitor_name' => 'Carlos Convidado',
            'visitor_document' => '12345678900',
            'visit_date' => now()->addDay(),
            'visit_time' => '15:00',
            'status' => 'authorized',
            'purpose' => 'Jantar',
        ]);

        $this->assertNotNull($accessRequest->invite_token);

        // 1. Acesso público sem login via token
        $publicResponse = $this->getJson("/api/public/invites/{$accessRequest->invite_token}");
        $publicResponse->assertOk()
            ->assertJsonPath('visitor_name', 'Carlos Convidado')
            ->assertJsonPath('apartment.number', '101');

        // 2. Busca pela portaria autenticada
        $gatehouseResponse = $this->getJson("/api/access-control/access-requests/by-token/{$accessRequest->invite_token}");
        $gatehouseResponse->assertOk()
            ->assertJsonPath('id', $accessRequest->id)
            ->assertJsonPath('visitor_name', 'Carlos Convidado');
    }

    public function test_package_show_by_code_for_express_checkout(): void
    {
        $package = Package::create([
            'condominium_id' => $this->condominium->id,
            'apartment_id' => $this->apartment->id,
            'recipient_name' => 'Morador Teste',
            'recipient_apartment' => '101',
            'sender' => 'Amazon',
            'pickup_location' => 'Portaria Principal',
            'code' => 'ABC12345',
        ]);

        $response = $this->getJson('/api/packages/by-code/ABC12345');
        $response->assertOk()
            ->assertJsonPath('id', $package->id)
            ->assertJsonPath('code', 'ABC12345');
    }
}
