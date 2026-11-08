<?php

namespace Tests\Unit;

use App\Enums\TransactionType;
use App\Models\CashFlow;
use App\Models\Condominium;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relacionamento_com_condominio(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alguns recursos necessários.');
        }
        $condo = Condominium::factory()->create();
        $transaction = Transaction::factory()->create(['condominium_id' => $condo->id, 'type' => TransactionType::RECEITA]);
        $flow = CashFlow::factory()->create([
            'condominium_id' => $condo->id,
            'transaction_id' => $transaction->id,
            'type' => $transaction->type,
        ]);

        $this->assertEquals($condo->id, $flow->condominium->id);
        $this->assertEquals($transaction->id, $flow->transaction->id);
    }
}
