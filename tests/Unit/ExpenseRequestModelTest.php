<?php

namespace Tests\Unit;

use App\Models\Condominium;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseRequestModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relacionamento_com_condominio(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alguns recursos necessários.');
        }
        $condo = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condo->id]);
        $request = ExpenseRequest::factory()->create([
            'condominium_id' => $condo->id,
            'requested_by' => $user->id,
        ]);

        $this->assertEquals($condo->id, $request->condominium->id);
        $this->assertEquals($user->id, $request->requester->id);
    }
}
