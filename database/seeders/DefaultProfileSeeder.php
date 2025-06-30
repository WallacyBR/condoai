<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Module;
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

        // Associar todos os módulos ao perfil SysOp
        $sysOpProfile = Profile::where('name', 'SysOp')->first();
        $allModules = Module::all();
        
        if ($sysOpProfile && $allModules->count() > 0) {
            $syncData = $allModules->mapWithKeys(function ($m) {
                return [$m->id => [
                    'can_view' => true,
                    'can_create' => true,
                    'can_update' => true,
                    'can_delete' => true,
                    'scope' => 'all',
                ]];
            })->toArray();
            $sysOpProfile->modules()->sync($syncData);
        }
    }
}
