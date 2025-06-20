<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Condominium;
use App\Models\Tower;
use App\Models\Block;
use App\Models\Apartment;
use App\Models\Resident;
use App\Enums\ResidentClassification;

class CustomCondominiumResidentsSeeder extends Seeder
{
    public function run(): void
    {
        // Condomínio 1: com torres, blocos e apartamentos
        $condo1 = Condominium::create([
            'name' => 'Condomínio Completo',
            'document' => '11.111.111/1111-11',
            'address' => 'Rua Completa, 100',
            'city' => 'Cidade 1',
            'state' => 'SP',
            'zip_code' => '01000-000',
            'phone' => '(11) 1111-1111',
            'email' => 'contato@completo.com',
            'responsible' => 'Síndico Completo',
            'active' => true,
            'domain' => 'completo.com',
        ]);

        $towerA = Tower::create([
            'name' => 'Torre A',
            'condominium_id' => $condo1->id,
            'active' => true,
        ]);
        $blockA1 = Block::create([
            'name' => 'Bloco A1',
            'condominium_id' => $condo1->id,
            'tower_id' => $towerA->id,
            'active' => true,
        ]);
        $blockA2 = Block::create([
            'name' => 'Bloco A2',
            'condominium_id' => $condo1->id,
            'tower_id' => $towerA->id,
            'active' => true,
        ]);

        // Apartamentos do bloco A1
        $apA1_101 = Apartment::create([
            'number' => '101',
            'floor' => 1,
            'condominium_id' => $condo1->id,
            'block_id' => $blockA1->id,
            'tower_id' => $towerA->id,
            'active' => true,
        ]);
        $apA1_102 = Apartment::create([
            'number' => '102',
            'floor' => 1,
            'condominium_id' => $condo1->id,
            'block_id' => $blockA1->id,
            'tower_id' => $towerA->id,
            'active' => true,
        ]);
        // Apartamentos do bloco A2
        $apA2_201 = Apartment::create([
            'number' => '201',
            'floor' => 2,
            'condominium_id' => $condo1->id,
            'block_id' => $blockA2->id,
            'tower_id' => $towerA->id,
            'active' => true,
        ]);

        // Condomínio 2: só blocos e apartamentos
        $condo2 = Condominium::create([
            'name' => 'Condomínio Simples',
            'document' => '22.222.222/2222-22',
            'address' => 'Rua Simples, 200',
            'city' => 'Cidade 2',
            'state' => 'RJ',
            'zip_code' => '02000-000',
            'phone' => '(21) 2222-2222',
            'email' => 'contato@simples.com',
            'responsible' => 'Síndico Simples',
            'active' => true,
            'domain' => 'simples.com',
        ]);

        $blockB1 = Block::create([
            'name' => 'Bloco B1',
            'condominium_id' => $condo2->id,
            'active' => true,
        ]);
        $blockB2 = Block::create([
            'name' => 'Bloco B2',
            'condominium_id' => $condo2->id,
            'active' => true,
        ]);

        $apB1_101 = Apartment::create([
            'number' => '101',
            'floor' => 1,
            'condominium_id' => $condo2->id,
            'block_id' => $blockB1->id,
            'active' => true,
        ]);
        $apB2_201 = Apartment::create([
            'number' => '201',
            'floor' => 2,
            'condominium_id' => $condo2->id,
            'block_id' => $blockB2->id,
            'active' => true,
        ]);

        // Moradores para cada apartamento
        // Condomínio 1
        $owner1 = Resident::create([
            'name' => 'João Proprietário',
            'email' => 'joao@completo.com',
            'cell_phone' => '(11) 90000-0001',
            'classification' => ResidentClassification::PROPRIETARIO,
            'active' => true,
            'condominium_id' => $condo1->id,
        ]);
        $tenant1 = Resident::create([
            'name' => 'Maria Inquilina',
            'email' => 'maria@completo.com',
            'cell_phone' => '(11) 90000-0002',
            'classification' => ResidentClassification::INQUILINO,
            'active' => true,
            'condominium_id' => $condo1->id,
        ]);
        $resident1 = Resident::create([
            'name' => 'Carlos Morador',
            'email' => 'carlos@completo.com',
            'cell_phone' => '(11) 90000-0003',
            'classification' => ResidentClassification::MORADOR,
            'active' => true,
            'condominium_id' => $condo1->id,
        ]);
        // Associar moradores ao apartamento 101 do bloco A1
        $apA1_101->residents()->attach($owner1->id, ['type' => 'proprietario', 'active' => true]);
        $apA1_101->residents()->attach($tenant1->id, ['type' => 'inquilino', 'active' => true]);
        $apA1_101->residents()->attach($resident1->id, ['type' => 'morador', 'active' => true]);

        // Condomínio 2
        $owner2 = Resident::create([
            'name' => 'Ana Proprietária',
            'email' => 'ana@simples.com',
            'cell_phone' => '(21) 90000-0004',
            'classification' => ResidentClassification::PROPRIETARIO,
            'active' => true,
            'condominium_id' => $condo2->id,
        ]);
        $tenant2 = Resident::create([
            'name' => 'Pedro Inquilino',
            'email' => 'pedro@simples.com',
            'cell_phone' => '(21) 90000-0005',
            'classification' => ResidentClassification::INQUILINO,
            'active' => true,
            'condominium_id' => $condo2->id,
        ]);
        $resident2 = Resident::create([
            'name' => 'Lucia Moradora',
            'email' => 'lucia@simples.com',
            'cell_phone' => '(21) 90000-0006',
            'classification' => ResidentClassification::MORADOR,
            'active' => true,
            'condominium_id' => $condo2->id,
        ]);
        // Associar moradores ao apartamento 101 do bloco B1
        $apB1_101->residents()->attach($owner2->id, ['type' => 'proprietario', 'active' => true]);
        $apB1_101->residents()->attach($tenant2->id, ['type' => 'inquilino', 'active' => true]);
        $apB1_101->residents()->attach($resident2->id, ['type' => 'morador', 'active' => true]);
    }
} 