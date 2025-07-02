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
        $condo1 = Condominium::firstOrCreate(
            ['document' => '11.111.111/1111-11'],
            [
                'name' => 'Condomínio Completo',
                'address' => 'Rua Completa, 100',
                'city' => 'Cidade 1',
                'state' => 'SP',
                'zip_code' => '01000-000',
                'phone' => '(11) 1111-1111',
                'email' => 'contato@completo.com',
                'responsible' => 'Síndico Completo',
                'active' => true,
                'domain' => 'completo.com',
            ]
        );

        $towerA = Tower::firstOrCreate(
            ['name' => 'Torre A', 'condominium_id' => $condo1->id],
            ['active' => true]
        );
        
        $blockA1 = Block::firstOrCreate(
            ['name' => 'Bloco A1', 'condominium_id' => $condo1->id, 'tower_id' => $towerA->id],
            ['active' => true]
        );
        
        $blockA2 = Block::firstOrCreate(
            ['name' => 'Bloco A2', 'condominium_id' => $condo1->id, 'tower_id' => $towerA->id],
            ['active' => true]
        );

        // Apartamentos do bloco A1
        $apA1_101 = Apartment::firstOrCreate(
            ['number' => '101', 'block_id' => $blockA1->id, 'condominium_id' => $condo1->id],
            [
                'floor' => 1,
                'tower_id' => $towerA->id,
                'active' => true,
            ]
        );
        
        $apA1_102 = Apartment::firstOrCreate(
            ['number' => '102', 'block_id' => $blockA1->id, 'condominium_id' => $condo1->id],
            [
                'floor' => 1,
                'tower_id' => $towerA->id,
                'active' => true,
            ]
        );
        
        // Apartamentos do bloco A2
        $apA2_201 = Apartment::firstOrCreate(
            ['number' => '201', 'block_id' => $blockA2->id, 'condominium_id' => $condo1->id],
            [
                'floor' => 2,
                'tower_id' => $towerA->id,
                'active' => true,
            ]
        );

        // Condomínio 2: só blocos e apartamentos
        $condo2 = Condominium::firstOrCreate(
            ['document' => '22.222.222/2222-22'],
            [
                'name' => 'Condomínio Simples',
                'address' => 'Rua Simples, 200',
                'city' => 'Cidade 2',
                'state' => 'RJ',
                'zip_code' => '02000-000',
                'phone' => '(21) 2222-2222',
                'email' => 'contato@simples.com',
                'responsible' => 'Síndico Simples',
                'active' => true,
                'domain' => 'simples.com',
            ]
        );

        $blockB1 = Block::firstOrCreate(
            ['name' => 'Bloco B1', 'condominium_id' => $condo2->id],
            ['active' => true]
        );
        
        $blockB2 = Block::firstOrCreate(
            ['name' => 'Bloco B2', 'condominium_id' => $condo2->id],
            ['active' => true]
        );

        $apB1_101 = Apartment::firstOrCreate(
            ['number' => '101', 'block_id' => $blockB1->id, 'condominium_id' => $condo2->id],
            [
                'floor' => 1,
                'active' => true,
            ]
        );
        
        $apB2_201 = Apartment::firstOrCreate(
            ['number' => '201', 'block_id' => $blockB2->id, 'condominium_id' => $condo2->id],
            [
                'floor' => 2,
                'active' => true,
            ]
        );

        // Moradores para cada apartamento
        // Condomínio 1
        $owner1 = Resident::firstOrCreate(
            ['email' => 'joao@completo.com', 'condominium_id' => $condo1->id],
            [
                'name' => 'João Proprietário',
                'cell_phone' => '(11) 90000-0001',
                'classification' => ResidentClassification::PROPRIETARIO,
                'active' => true,
            ]
        );
        
        $tenant1 = Resident::firstOrCreate(
            ['email' => 'maria@completo.com', 'condominium_id' => $condo1->id],
            [
                'name' => 'Maria Inquilina',
                'cell_phone' => '(11) 90000-0002',
                'classification' => ResidentClassification::INQUILINO,
                'active' => true,
            ]
        );
        
        $resident1 = Resident::firstOrCreate(
            ['email' => 'carlos@completo.com', 'condominium_id' => $condo1->id],
            [
                'name' => 'Carlos Morador',
                'cell_phone' => '(11) 90000-0003',
                'classification' => ResidentClassification::MORADOR,
                'active' => true,
            ]
        );

        // Associar moradores ao apartamento 101 do bloco A1 (evitar duplicatas)
        if (!$apA1_101->residents()->where('resident_id', $owner1->id)->exists()) {
            $apA1_101->residents()->attach($owner1->id, ['type' => 'proprietario', 'active' => true]);
        }
        if (!$apA1_101->residents()->where('resident_id', $tenant1->id)->exists()) {
            $apA1_101->residents()->attach($tenant1->id, ['type' => 'inquilino', 'active' => true]);
        }
        if (!$apA1_101->residents()->where('resident_id', $resident1->id)->exists()) {
            $apA1_101->residents()->attach($resident1->id, ['type' => 'morador', 'active' => true]);
        }

        // Condomínio 2
        $owner2 = Resident::firstOrCreate(
            ['email' => 'ana@simples.com', 'condominium_id' => $condo2->id],
            [
                'name' => 'Ana Proprietária',
                'cell_phone' => '(21) 90000-0004',
                'classification' => ResidentClassification::PROPRIETARIO,
                'active' => true,
            ]
        );
        
        $tenant2 = Resident::firstOrCreate(
            ['email' => 'pedro@simples.com', 'condominium_id' => $condo2->id],
            [
                'name' => 'Pedro Inquilino',
                'cell_phone' => '(21) 90000-0005',
                'classification' => ResidentClassification::INQUILINO,
                'active' => true,
            ]
        );
        
        $resident2 = Resident::firstOrCreate(
            ['email' => 'lucia@simples.com', 'condominium_id' => $condo2->id],
            [
                'name' => 'Lucia Moradora',
                'cell_phone' => '(21) 90000-0006',
                'classification' => ResidentClassification::MORADOR,
                'active' => true,
            ]
        );

        // Associar moradores ao apartamento 101 do bloco B1 (evitar duplicatas)
        if (!$apB1_101->residents()->where('resident_id', $owner2->id)->exists()) {
            $apB1_101->residents()->attach($owner2->id, ['type' => 'proprietario', 'active' => true]);
        }
        if (!$apB1_101->residents()->where('resident_id', $tenant2->id)->exists()) {
            $apB1_101->residents()->attach($tenant2->id, ['type' => 'inquilino', 'active' => true]);
        }
        if (!$apB1_101->residents()->where('resident_id', $resident2->id)->exists()) {
            $apB1_101->residents()->attach($resident2->id, ['type' => 'morador', 'active' => true]);
        }

        $this->command->info('Condomínios, torres, blocos, apartamentos e moradores criados com sucesso!');
    }
} 