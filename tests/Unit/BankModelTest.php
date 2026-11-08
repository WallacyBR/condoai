<?php

namespace Tests\Unit;

use App\Models\Bank;
use App\Models\Condominium;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relacionamento_com_condominio(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alguns recursos necessários.');
        }
        $condo = Condominium::factory()->create();
        $bank = Bank::factory()->create(['condominium_id' => $condo->id]);

        $this->assertEquals($condo->id, $bank->condominium->id);
    }
}
