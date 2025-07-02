<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Condominium;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar perfil SysOp ou criar um padrão
        $profile = Profile::where('name', 'SysOp')->first();
        if (!$profile) {
            $this->command->warn('Perfil SysOp não encontrado. Usando o primeiro perfil disponível.');
            $profile = Profile::first();
        }

        if (!$profile) {
            $this->command->error('Nenhum perfil encontrado! Execute o DefaultProfileSeeder primeiro.');
            return;
        }

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

        // Verificar se o usuário já existe e atualizar se necessário
        $user = User::where('email', 'teste@teste.com')->first();
        
        if ($user) {
            // Atualizar o usuário existente
            $user->update([
                'password' => Hash::make('teste'),
                'profile_id' => $profile->id,
                'condominium_id' => $condo->id,
            ]);
            $this->command->info('Usuário teste atualizado com sucesso.');
        } else {
            // Criar novo usuário
            User::create([
                'name' => 'Usuário Teste',
                'email' => 'teste@teste.com',
                'password' => Hash::make('teste'),
                'profile_id' => $profile->id,
                'condominium_id' => $condo->id,
            ]);
            $this->command->info('Usuário teste criado com sucesso.');
        }

        // Criar usuário administrador se não existir
        $adminUser = User::where('email', 'admin@sistema.com')->first();
        if (!$adminUser) {
            User::create([
                'name' => 'Administrador Sistema',
                'email' => 'admin@sistema.com',
                'password' => Hash::make('admin123'),
                'profile_id' => $profile->id,
                'condominium_id' => $condo->id,
            ]);
            $this->command->info('Usuário administrador criado com sucesso.');
        }
    }
}
