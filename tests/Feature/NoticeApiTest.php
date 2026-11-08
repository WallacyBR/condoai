<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeApiTest extends TestCase
{
    use RefreshDatabase;

    private $condominioId;

    private function autenticar(): string
    {
        $condominio = Condominium::factory()->create();
        $this->condominioId = $condominio->id;

        $usuario = User::factory()->create([
            'password' => bcrypt('segredo'),
            'condominium_id' => $condominio->id,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $usuario->email,
            'password' => 'segredo',
        ]);

        return $login['authorization']['token'];
    }

    public function test_criar_aviso(): void
    {
        $token = $this->autenticar();

        $dados = [
            'title' => 'Reunião Extraordinária',
            'body' => 'Convocamos todos para a reunião desta sexta-feira.',
            'send_to_all' => true,
        ];

        $resposta = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/notices', $dados);

        $resposta->assertStatus(201)
            ->assertJsonFragment(['title' => 'Reunião Extraordinária']);
    }

    public function test_visualizar_aviso(): void
    {
        $token = $this->autenticar();

        $aviso = Notice::factory()->create([
            'condominium_id' => $this->condominioId,
        ]);

        $resposta = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/notices/'.$aviso->id);

        $resposta->assertStatus(200)
            ->assertJsonFragment(['id' => $aviso->id]);
    }
}

