<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Condominium;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $condo = Condominium::firstOrCreate(
            ['document' => '00.000.000/0001-00'],
            [
                'name' => 'Condomínio Teste',
                'address' => 'Rua Exemplo, 123',
                'city' => 'Cidade Exemplo',
                'state' => 'EX',
                'zip_code' => '00000-000',
                'phone' => '(11) 99999-9999',
                'email' => 'contato@condominio.com',
                'responsible' => 'Síndico Exemplo',
                'active' => true,
                'domain' => 'condominio-teste.com',
            ]
        );

        // Verificar se o usuário já existe e atualizar a senha se necessário
        $user = User::where('email', 'teste@teste.com')->first();
        
        if ($user) {
            // Atualizar a senha do usuário existente
            $user->update([
                'password' => Hash::make('teste'),
                'condominium_id' => $condo->id,
            ]);
        } else {
            // Criar novo usuário
            User::create([
                'name' => 'Usuário Teste',
                'email' => 'teste@teste.com',
                'password' => Hash::make('teste'),
                'profile_id' => 1, // Assumindo que 1 é o ID do perfil padrão
                'condominium_id' => $condo->id,
            ]);
        }
    }
}
