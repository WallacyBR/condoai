<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommonArea;
use App\Models\Condominium;

class CommonAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $condominiums = Condominium::all();
        
        if ($condominiums->isEmpty()) {
            $this->command->warn('Nenhum condomínio encontrado. Execute primeiro o CustomCondominiumResidentsSeeder.');
            return;
        }

        $commonAreas = [
            [
                'name' => 'Salão de Festas',
                'description' => 'Espaço para eventos e comemorações',
                'capacity' => 80,
                'price_per_hour' => 150.00,
                'requires_approval' => true,
                'rules' => 'Proibido som após 22h. Limpeza por conta do locatário.',
                'min_booking_hours' => 4,
                'max_booking_hours' => 12,
                'availability_schedule' => json_encode([
                    'monday' => ['08:00', '23:00'],
                    'tuesday' => ['08:00', '23:00'],
                    'wednesday' => ['08:00', '23:00'],
                    'thursday' => ['08:00', '23:00'],
                    'friday' => ['08:00', '23:00'],
                    'saturday' => ['08:00', '23:00'],
                    'sunday' => ['08:00', '23:00'],
                ]),
            ],
            [
                'name' => 'Churrasqueira',
                'description' => 'Área gourmet com churrasqueira',
                'capacity' => 20,
                'price_per_hour' => 50.00,
                'requires_approval' => true,
                'rules' => 'Trazer carvão próprio. Limpeza obrigatória após uso.',
                'min_booking_hours' => 2,
                'max_booking_hours' => 8,
                'availability_schedule' => json_encode([
                    'monday' => ['08:00', '22:00'],
                    'tuesday' => ['08:00', '22:00'],
                    'wednesday' => ['08:00', '22:00'],
                    'thursday' => ['08:00', '22:00'],
                    'friday' => ['08:00', '22:00'],
                    'saturday' => ['08:00', '22:00'],
                    'sunday' => ['08:00', '22:00'],
                ]),
            ],
            [
                'name' => 'Quadra Poliesportiva',
                'description' => 'Quadra para práticas esportivas',
                'capacity' => 40,
                'price_per_hour' => 30.00,
                'requires_approval' => false,
                'rules' => 'Usar apenas tênis apropriado. Manter área limpa.',
                'min_booking_hours' => 1,
                'max_booking_hours' => 4,
                'availability_schedule' => json_encode([
                    'monday' => ['06:00', '22:00'],
                    'tuesday' => ['06:00', '22:00'],
                    'wednesday' => ['06:00', '22:00'],
                    'thursday' => ['06:00', '22:00'],
                    'friday' => ['06:00', '22:00'],
                    'saturday' => ['06:00', '22:00'],
                    'sunday' => ['06:00', '22:00'],
                ]),
            ],
            [
                'name' => 'Piscina Adulto',
                'description' => 'Piscina para adultos',
                'capacity' => 50,
                'price_per_hour' => 0.00,
                'requires_approval' => false,
                'rules' => 'Crianças menores de 12 anos devem estar acompanhadas.',
                'min_booking_hours' => 1,
                'max_booking_hours' => 8,
                'availability_schedule' => json_encode([
                    'monday' => ['06:00', '22:00'],
                    'tuesday' => ['06:00', '22:00'],
                    'wednesday' => ['06:00', '22:00'],
                    'thursday' => ['06:00', '22:00'],
                    'friday' => ['06:00', '22:00'],
                    'saturday' => ['06:00', '22:00'],
                    'sunday' => ['06:00', '22:00'],
                ]),
            ],
            [
                'name' => 'Playground',
                'description' => 'Área recreativa infantil',
                'capacity' => 25,
                'price_per_hour' => 0.00,
                'requires_approval' => false,
                'rules' => 'Crianças devem estar sempre acompanhadas por responsável.',
                'min_booking_hours' => 1,
                'max_booking_hours' => 6,
                'availability_schedule' => json_encode([
                    'monday' => ['06:00', '20:00'],
                    'tuesday' => ['06:00', '20:00'],
                    'wednesday' => ['06:00', '20:00'],
                    'thursday' => ['06:00', '20:00'],
                    'friday' => ['06:00', '20:00'],
                    'saturday' => ['06:00', '20:00'],
                    'sunday' => ['06:00', '20:00'],
                ]),
            ],
            [
                'name' => 'Academia',
                'description' => 'Espaço fitness com equipamentos',
                'capacity' => 15,
                'price_per_hour' => 0.00,
                'requires_approval' => false,
                'rules' => 'Usar toalha nos equipamentos. Limpar após uso.',
                'min_booking_hours' => 1,
                'max_booking_hours' => 3,
                'availability_schedule' => json_encode([
                    'monday' => ['05:00', '23:00'],
                    'tuesday' => ['05:00', '23:00'],
                    'wednesday' => ['05:00', '23:00'],
                    'thursday' => ['05:00', '23:00'],
                    'friday' => ['05:00', '23:00'],
                    'saturday' => ['05:00', '23:00'],
                    'sunday' => ['05:00', '23:00'],
                ]),
            ],
        ];

        foreach ($condominiums as $condominium) {
            foreach ($commonAreas as $area) {
                CommonArea::firstOrCreate(
                    [
                        'name' => $area['name'],
                        'condominium_id' => $condominium->id,
                    ],
                    array_merge($area, [
                        'condominium_id' => $condominium->id,
                        'active' => true,
                        'allow_custom_periods' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $this->command->info('Áreas comuns criadas com sucesso para ' . $condominiums->count() . ' condomínio(s)!');
    }
} 