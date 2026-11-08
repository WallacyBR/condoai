<?php

namespace Tests\Feature;

use App\Enums\OccurrencePriority;
use App\Enums\OccurrenceStatus;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Occurrence;
use App\Models\Package;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class OperationalReportControllerTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    private Condominium $condominium;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();
        $this->user = User::factory()->create(['condominium_id' => $this->condominium->id]);

        $this->actingAsTenant($this->user, $this->condominium);
    }

    public function test_returns_common_report_shape_for_all_operational_types(): void
    {
        foreach (['overview', 'residents', 'packages', 'occurrences', 'parking', 'bookings', 'maintenance', 'access'] as $type) {
            $this->getJson("/api/reports/operational?type={$type}&start_date=2026-06-01&end_date=2026-06-30")
                ->assertOk()
                ->assertJsonStructure([
                    'type',
                    'title',
                    'start_date',
                    'end_date',
                    'generated_at',
                    'summary' => [['label', 'value', 'tone']],
                    'columns' => [['key', 'label']],
                    'rows',
                ])
                ->assertJsonPath('type', $type);
        }
    }

    public function test_overview_uses_current_condominium_data_only(): void
    {
        $apartment = $this->createApartment($this->condominium, ['number' => '101']);
        Package::factory()->create([
            'apartment_id' => $apartment->id,
            'condominium_id' => $this->condominium->id,
            'recipient_name' => 'Morador Atual',
            'recipient_apartment' => '101',
            'sender' => 'Portaria',
            'created_at' => '2026-06-10 10:00:00',
            'retrieved_at' => null,
            'user_id' => $this->user->id,
        ]);

        $otherCondominium = Condominium::factory()->create();
        $otherApartment = $this->createApartment($otherCondominium, ['number' => '999']);
        Package::factory()->create([
            'apartment_id' => $otherApartment->id,
            'condominium_id' => $otherCondominium->id,
            'recipient_name' => 'Outro Condomínio',
            'recipient_apartment' => '999',
            'sender' => 'Portaria',
            'created_at' => '2026-06-10 10:00:00',
            'retrieved_at' => null,
            'user_id' => User::factory()->create(['condominium_id' => $otherCondominium->id])->id,
        ]);

        $this->getJson('/api/reports/operational?type=packages&start_date=2026-06-01&end_date=2026-06-30')
            ->assertOk()
            ->assertJsonPath('summary.0.value', '1')
            ->assertJsonFragment(['recipient' => 'Morador Atual'])
            ->assertJsonMissing(['recipient' => 'Outro Condomínio']);
    }

    public function test_occurrences_report_returns_rows_and_summary(): void
    {
        Occurrence::factory()->create([
            'condominium_id' => $this->condominium->id,
            'user_id' => $this->user->id,
            'title' => 'Elevador parado',
            'status' => OccurrenceStatus::OPEN,
            'priority' => OccurrencePriority::URGENT,
            'created_at' => '2026-06-12 09:00:00',
        ]);

        $this->getJson('/api/reports/operational?type=occurrences&start_date=2026-06-01&end_date=2026-06-30')
            ->assertOk()
            ->assertJsonPath('summary.2.value', '1')
            ->assertJsonFragment(['title' => 'Elevador parado']);
    }

    public function test_exports_report_as_csv(): void
    {
        $apartment = $this->createApartment($this->condominium, ['number' => '102']);
        Resident::factory()->create([
            'condominium_id' => $this->condominium->id,
            'apartment_id' => $apartment->id,
            'name' => 'Ana Relatório',
            'active' => true,
        ]);

        $response = $this->get('/api/reports/operational/export?type=residents&start_date=2026-06-01&end_date=2026-06-30');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Nome;Classificação;Status;Unidade;Telefone;Email', $csv);
        $this->assertStringContainsString('Ana Relatório', $csv);
    }

    private function createApartment(Condominium $condominium, array $attributes = []): Apartment
    {
        $block = Block::factory()->create(['condominium_id' => $condominium->id]);

        return Apartment::factory()->create(array_merge([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
            'owner_id' => null,
            'tenant_id' => null,
        ], $attributes));
    }
}
