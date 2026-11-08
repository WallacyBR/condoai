<?php

namespace Database\Seeders;

use App\Enums\ResidentClassification;
use App\Models\Condominium;
use App\Models\Resident;
use Illuminate\Database\Seeder;

class ResidentClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar um condomínio existente ou criar um novo
        $condominium = Condominium::firstOrCreate(
            ['domain' => 'teste-classificacao.com'],
            [
                'name' => 'Condomínio Teste Classificação',
                'document' => '11.111.111/1111-11',
                'address' => 'Rua Teste Classificação, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567',
                'phone' => '(11) 1234-5678',
                'email' => 'contato@teste-classificacao.com',
                'responsible' => 'Síndico Teste',
                'active' => true,
            ]
        );

        // Criar moradores com diferentes classificações
        $residents = [
            [
                'name' => 'João Silva',
                'email' => 'joao.silva@email.com',
                'cell_phone' => '(11) 99999-1111',
                'classification' => ResidentClassification::PROPRIETARIO,
                'active' => true,
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@email.com',
                'cell_phone' => '(11) 99999-2222',
                'classification' => ResidentClassification::MORADOR,
                'active' => true,
            ],
            [
                'name' => 'Pedro Costa',
                'email' => 'pedro.costa@email.com',
                'cell_phone' => '(11) 99999-3333',
                'classification' => ResidentClassification::INQUILINO,
                'active' => true,
            ],
            [
                'name' => 'Ana Oliveira',
                'email' => 'ana.oliveira@email.com',
                'cell_phone' => '(11) 99999-4444',
                'classification' => ResidentClassification::PROPRIETARIO,
                'active' => false,
            ],
            [
                'name' => 'Carlos Ferreira',
                'email' => 'carlos.ferreira@email.com',
                'cell_phone' => '(11) 99999-5555',
                'classification' => ResidentClassification::MORADOR,
                'active' => true,
            ],
            [
                'name' => 'Lucia Pereira',
                'email' => 'lucia.pereira@email.com',
                'cell_phone' => '(11) 99999-6666',
                'classification' => ResidentClassification::INQUILINO,
                'active' => false,
            ],
        ];

        foreach ($residents as $residentData) {
            Resident::firstOrCreate(
                ['email' => $residentData['email']],
                array_merge($residentData, ['condominium_id' => $condominium->id])
            );
        }

        $this->command->info('Moradores com classificações criados com sucesso!');
        $this->command->info('Proprietários: ' . Resident::where('classification', ResidentClassification::PROPRIETARIO)->count());
        $this->command->info('Moradores: ' . Resident::where('classification', ResidentClassification::MORADOR)->count());
        $this->command->info('Inquilinos: ' . Resident::where('classification', ResidentClassification::INQUILINO)->count());
    }
}
