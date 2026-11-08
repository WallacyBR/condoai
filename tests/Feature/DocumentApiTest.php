<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentApiTest extends TestCase
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

    public function test_listar_documentos(): void
    {
        $token = $this->autenticar();

        $aviso = Notice::factory()->create([
            'condominium_id' => $this->condominioId,
        ]);
        NoticeAttachment::factory()->create(['notice_id' => $aviso->id]);

        $resposta = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/documents');

        $resposta->assertStatus(200)
            ->assertJsonFragment(['notice_id' => $aviso->id]);
    }
}

