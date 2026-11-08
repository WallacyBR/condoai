<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MaintenanceArea;
use App\Models\Condominium;

class MaintenanceAreasSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Buscar o primeiro condomínio ou criar um padrão
        $condominium = Condominium::first();
        
        if (!$condominium) {
            $condominium = Condominium::create([
                'name' => 'Condomínio Exemplo',
                'address' => 'Rua Exemplo, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567',
                'phone' => '(11) 1234-5678',
                'email' => 'exemplo@condominio.com.br',
            ]);
        }

        $areas = [
            [
                'name' => 'Elevadores',
                'description' => 'Área responsável pela manutenção de elevadores',
                'condominium_id' => $condominium->id,
            ],
            [
                'name' => 'Piscina',
                'description' => 'Área responsável pela manutenção da piscina e equipamentos aquáticos',
                'condominium_id' => $condominium->id,
            ],
            [
                'name' => 'Jardim',
                'description' => 'Área responsável pela manutenção de jardins e paisagismo',
                'condominium_id' => $condominium->id,
            ],
            [
                'name' => 'Portaria',
                'description' => 'Área responsável pela manutenção da portaria e sistemas de segurança',
                'condominium_id' => $condominium->id,
            ],
            [
                'name' => 'Salão de Festas',
                'description' => 'Área responsável pela manutenção do salão de festas',
                'condominium_id' => $condominium->id,
            ],
            [
                'name' => 'Garagem',
                'description' => 'Área responsável pela manutenção da garagem e sistema de portões',
                'condominium_id' => $condominium->id,
            ]
        ];

        foreach ($areas as $area) {
            MaintenanceArea::updateOrCreate(
                ['name' => $area['name'], 'condominium_id' => $area['condominium_id']],
                $area
            );
        }

        $this->command->info('Áreas de manutenção criadas com sucesso!');
    }
}
