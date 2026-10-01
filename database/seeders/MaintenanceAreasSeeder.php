<?php

namespace Database\Seeders;

use App\Models\Condominium;
use App\Models\MaintenanceArea;
use Illuminate\Database\Seeder;

class MaintenanceAreasSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Buscar condomínios existentes primeiro
        $condominiums = Condominium::all();

        if ($condominiums->isEmpty()) {
            // Criar condomínio de exemplo apenas se não existir nenhum
            $condominium = Condominium::create([
                'name' => 'Condomínio Exemplo Manutenção',
                'document' => '99.999.999/9999-99',
                'address' => 'Rua Manutenção, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567',
                'phone' => '(11) 1234-5678',
                'email' => 'manutencao@condominio.com.br',
                'responsible' => 'Síndico Manutenção',
                'active' => true,
                'domain' => 'manutencao-exemplo.com',
            ]);
            $condominiums = collect([$condominium]);
        }

        $areas = [
            [
                'name' => 'Elevadores',
                'description' => 'Área responsável pela manutenção de elevadores',
            ],
            [
                'name' => 'Piscina',
                'description' => 'Área responsável pela manutenção da piscina e equipamentos aquáticos',
            ],
            [
                'name' => 'Jardim',
                'description' => 'Área responsável pela manutenção de jardins e paisagismo',
            ],
            [
                'name' => 'Portaria',
                'description' => 'Área responsável pela manutenção da portaria e sistemas de segurança',
            ],
            [
                'name' => 'Salão de Festas',
                'description' => 'Área responsável pela manutenção do salão de festas',
            ],
            [
                'name' => 'Garagem',
                'description' => 'Área responsável pela manutenção da garagem e sistema de portões',
            ],
            [
                'name' => 'Playground',
                'description' => 'Área responsável pela manutenção do playground e equipamentos infantis',
            ],
            [
                'name' => 'Quadra de Esportes',
                'description' => 'Área responsável pela manutenção da quadra poliesportiva',
            ],
        ];

        // Criar áreas para cada condomínio existente
        foreach ($condominiums as $condominium) {
            foreach ($areas as $area) {
                MaintenanceArea::updateOrCreate(
                    [
                        'name' => $area['name'],
                        'condominium_id' => $condominium->id,
                    ],
                    [
                        'description' => $area['description'],
                        'condominium_id' => $condominium->id,
                    ]
                );
            }
        }

        $this->command->info('Áreas de manutenção criadas com sucesso para '.$condominiums->count().' condomínio(s)!');
    }
}
