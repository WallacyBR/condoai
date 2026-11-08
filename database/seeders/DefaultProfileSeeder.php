<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;

class DefaultProfileSeeder extends Seeder
{
    public function run(): void
    {
        Profile::create([
            'name' => 'Administrador',
            'description' => 'Perfil com acesso total ao sistema'
        ]);
    }
} 