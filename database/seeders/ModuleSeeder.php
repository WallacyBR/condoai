<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

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
