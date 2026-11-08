<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PayableApiTest extends TestCase
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

    public function test_create_payable_with_attachment(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alguns recursos necessários.');
        }
        Storage::fake('public');

        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $account = ChartAccount::factory()->create(['condominium_id' => $condo->id]);
        $bank = Bank::factory()->create(['condominium_id' => $condo->id]);

        $file = UploadedFile::fake()->create('nota.pdf', 100, 'application/pdf');

        $data = [
            'condominium_id' => $condo->id,
            'chart_account_id' => $account->id,
            'bank_id' => $bank->id,
            'due_date' => now()->addDay()->format('Y-m-d'),
            'amount' => 150.75,
            'status' => 'pendente',
            'attachments' => [$file],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/finance/payables', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['amount' => '150.75']);
        Storage::disk('public')->assertExists('payables/' . $file->hashName());
    }
}
