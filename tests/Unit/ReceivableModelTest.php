<?php

namespace Tests\Unit;

use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Receivable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceivableModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relacionamento_com_condominio(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alguns recursos necessários.');
        }
        $condo = Condominium::factory()->create();
        $account = ChartAccount::factory()->create(['condominium_id' => $condo->id]);
        $receivable = Receivable::factory()->create([
            'condominium_id' => $condo->id,
            'chart_account_id' => $account->id,
        ]);

        $this->assertEquals($condo->id, $receivable->condominium->id);
        $this->assertEquals($account->id, $receivable->account->id);
    }
}
