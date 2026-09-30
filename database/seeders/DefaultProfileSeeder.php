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
            ['name' => 'Morador', 'description' => 'Morador do condomínio'],
        ];

        foreach ($profiles as $profile) {
            Profile::firstOrCreate(['name' => $profile['name']], $profile);
        }

        // Aguardar que os módulos sejam criados pelo ModuleSeeder
        // Depois associar permissões aos perfis
        $this->assignPermissionsToProfiles();
    }

    private function assignPermissionsToProfiles(): void
    {
        $allModules = Module::all();
        
        if ($allModules->isEmpty()) {
            $this->command->warn('Nenhum módulo encontrado. Execute o ModuleSeeder primeiro.');
            return;
        }

        // Perfil SysOp - acesso total a tudo
        $sysOpProfile = Profile::where('name', 'SysOp')->first();
        if ($sysOpProfile) {
            $syncData = $allModules->mapWithKeys(function ($module) {
                return [$module->id => [
                    'can_view' => true,
                    'can_create' => true,
                    'can_update' => true,
                    'can_delete' => true,
                    'scope' => 'all',
                ]];
            })->toArray();
            $sysOpProfile->modules()->sync($syncData);
        }

        // Perfil Administradora - acesso amplo mas limitado; Financeiro com CRUD completo
        $adminProfile = Profile::where('name', 'Administradora')->first();
        if ($adminProfile) {
            $syncData = $allModules->mapWithKeys(function ($module) {
                if ($module->name === 'Financeiro') {
                    return [$module->id => [
                        'can_view' => true,
                        'can_create' => true,
                        'can_update' => true,
                        'can_delete' => true,
                        'scope' => 'all',
                    ]];
                }

                return [$module->id => [
                    'can_view' => true,
                    'can_create' => in_array($module->name, ['Condomínios', 'Moradores', 'Unidades', 'Relatórios']),
                    'can_update' => in_array($module->name, ['Condomínios', 'Moradores', 'Unidades']),
                    'can_delete' => false,
                    'scope' => 'all',
                ]];
            })->toArray();
            $adminProfile->modules()->sync($syncData);
        }

        // Perfil Síndico - gestão do próprio condomínio
        $sindicoProfile = Profile::where('name', 'Síndico')->first();
        if ($sindicoProfile) {
            $syncData = $allModules->mapWithKeys(function ($module) {
                return [$module->id => [
                    'can_view' => true,
                    'can_create' => !in_array($module->name, ['Condomínios', 'Configurações']),
                    'can_update' => !in_array($module->name, ['Condomínios', 'Configurações']),
                    'can_delete' => in_array($module->name, ['Correspondências', 'Ocorrências', 'Financeiro']),
                    'scope' => 'own',
                ]];
            })->toArray();
            $sindicoProfile->modules()->sync($syncData);
        }

        // Perfil Zelador - operações básicas
        $zeladorProfile = Profile::where('name', 'Zelador')->first();
        if ($zeladorProfile) {
            $syncData = $allModules->whereIn('name', ['Correspondências', 'Ocorrências', 'Moradores', 'Financeiro'])
                ->mapWithKeys(function ($module) {
                    if ($module->name === 'Financeiro') {
                        return [$module->id => [
                            'can_view' => true,
                            'can_create' => false,
                            'can_update' => false,
                            'can_delete' => false,
                            'scope' => 'own',
                        ]];
                    }

                    return [$module->id => [
                        'can_view' => true,
                        'can_create' => in_array($module->name, ['Correspondências', 'Ocorrências']),
                        'can_update' => in_array($module->name, ['Correspondências', 'Ocorrências']),
                        'can_delete' => false,
                        'scope' => 'own',
                    ]];
                })->toArray();
            $zeladorProfile->modules()->sync($syncData);
        }

        // Perfil Morador - apenas visualização
        $moradorProfile = Profile::where('name', 'Morador')->first();
        if ($moradorProfile) {
            $syncData = $allModules->whereIn('name', ['Correspondências', 'Ocorrências'])
                ->mapWithKeys(function ($module) {
                    return [$module->id => [
                        'can_view' => true,
                        'can_create' => $module->name === 'Ocorrências',
                        'can_update' => false,
                        'can_delete' => false,
                        'scope' => 'own',
                    ]];
                })->toArray();
            $moradorProfile->modules()->sync($syncData);
        }
    }
}
