<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceReportApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): string
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);
        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);
        return $login['authorization']['token'];
    }

    public function test_generate_dre_pdf(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alguns recursos necessários.');
        }

        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $account = ChartAccount::factory()->create(['condominium_id' => $condo->id]);
        Transaction::factory()->create([
            'condominium_id' => $condo->id,
            'chart_account_id' => $account->id,
            'type' => TransactionType::RECEITA,
            'amount' => 100,
        ]);
        Transaction::factory()->create([
            'condominium_id' => $condo->id,
            'chart_account_id' => $account->id,
            'type' => TransactionType::DESPESA,
            'amount' => 50,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/api/finance/reports/dre?condominium_id='.$condo->id.'&start_date='.now()->subDay()->format('Y-m-d').'&end_date='.now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_generate_balancete_pdf(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alguns recursos necessários.');
        }

        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $account = ChartAccount::factory()->create(['condominium_id' => $condo->id]);
        Transaction::factory()->count(2)->create([
            'condominium_id' => $condo->id,
            'chart_account_id' => $account->id,
            'type' => TransactionType::RECEITA,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/api/finance/reports/balancete?condominium_id='.$condo->id.'&start_date='.now()->subDay()->format('Y-m-d').'&end_date='.now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }
}
