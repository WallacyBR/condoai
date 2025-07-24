<?php

namespace Tests\Unit;

use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartAccountModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relacionamentos_basicos(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alguns recursos necessários.');
        }
        $condo = Condominium::factory()->create();
        $parent = ChartAccount::factory()->create(['condominium_id' => $condo->id]);
        $child = ChartAccount::factory()->create([
            'condominium_id' => $condo->id,
            'parent_id' => $parent->id,
        ]);

        $transaction = Transaction::factory()->create([
            'condominium_id' => $condo->id,
            'chart_account_id' => $child->id,
        ]);

        $this->assertEquals($condo->id, $child->condominium->id);
        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertTrue($child->transactions->contains($transaction));
    }
}
