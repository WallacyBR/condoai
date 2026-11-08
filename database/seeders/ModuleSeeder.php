<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            ['name' => 'Condomínios', 'description' => 'Gestão de condomínios'],
            ['name' => 'Moradores', 'description' => 'Cadastro de moradores'],
            ['name' => 'Unidades', 'description' => 'Torres, blocos e apartamentos'],
            ['name' => 'Relatórios', 'description' => 'Geração de relatórios'],
            ['name' => 'Configurações', 'description' => 'Ajustes do sistema'],
        ];

        foreach ($modules as $module) {
            Module::firstOrCreate(['name' => $module['name']], $module);
        }
    }
}
