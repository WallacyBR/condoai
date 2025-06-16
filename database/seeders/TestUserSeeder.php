<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Usuário Teste',
            'email' => 'teste@teste.com',
            'password' => Hash::make('teste'),
            'profile_id' => 1, // Assumindo que 1 é o ID do perfil padrão
        ]);
    }
}
