<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

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
