<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;

class DefaultProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            ['name' => 'SysOp', 'description' => 'Administrador global do sistema'],
            ['name' => 'Administradora', 'description' => 'Empresa administradora de condomínios'],
            ['name' => 'Síndico', 'description' => 'Responsável pelo condomínio'],
            ['name' => 'Zelador', 'description' => 'Funcionário do condomínio'],
        ];

        foreach ($profiles as $profile) {
            Profile::firstOrCreate(['name' => $profile['name']], $profile);
        }
    }
}
