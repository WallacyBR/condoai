<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\User;
use App\Models\ChartAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Condominium $condominium;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();
        $this->user = User::factory()->create(['condominium_id' => $this->condominium->id]);
        $this->actingAs($this->user, 'api');
    }

    public function test_can_bulk_update_budget_items()
    {
        $chartAccount = ChartAccount::factory()->create(['condominium_id' => $this->condominium->id]);
        $year = 2025;

        $items = [
            [
                'chart_account_id' => $chartAccount->id,
                'month' => 1,
                'year' => $year,
                'amount' => 1000.50,
            ],
            [
                'chart_account_id' => $chartAccount->id,
                'month' => 2,
                'year' => $year,
                'amount' => 1200.75,
            ],
        ];

        $response = $this->postJson('/api/finance/budget-items/bulk', [
            'items' => $items,
            'condominium_id' => $this->condominium->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Orçamento atualizado com sucesso.']);

        $this->assertDatabaseHas('budget_items', [
            'condominium_id' => $this->condominium->id,
            'chart_account_id' => $chartAccount->id,
            'month' => 1,
            'year' => $year,
            'amount' => 1000.50,
        ]);

        $this->assertDatabaseHas('budget_items', [
            'condominium_id' => $this->condominium->id,
            'chart_account_id' => $chartAccount->id,
            'month' => 2,
            'year' => $year,
            'amount' => 1200.75,
        ]);
    }

    public function test_can_get_budget_report()
    {
        $chartAccount = ChartAccount::factory()->create(['condominium_id' => $this->condominium->id]);
        $year = 2025;

        // Create budget item
        \App\Models\BudgetItem::factory()->create([
            'condominium_id' => $this->condominium->id,
            'chart_account_id' => $chartAccount->id,
            'month' => 1,
            'year' => $year,
            'amount' => 1000,
        ]);

        // Create a paid payable for the same account and period
        \App\Models\Payable::factory()->create([
            'condominium_id' => $this->condominium->id,
            'chart_account_id' => $chartAccount->id,
            'due_date' => "{$year}-01-15",
            'paid_at' => "{$year}-01-20",
            'amount' => 750,
        ]);

        $response = $this->getJson("/api/finance/reports/budget?year={$year}&condominium_id={$this->condominium->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'chart_account_id' => $chartAccount->id,
                     'budgeted_amount' => 1000.0,
                     'actual_amount' => 750.0,
                     'variance' => 250.0,
                 ]);
    }
}
