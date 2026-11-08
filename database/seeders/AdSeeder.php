<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ad;
use App\Models\Resident;
use App\Models\Condominium;
use App\Enums\AdCategory;
use App\Enums\AdCondition;
use App\Enums\AdStatus;

class AdSeeder extends Seeder
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

        $residents = Resident::all();
        
        if ($residents->isEmpty()) {
            $this->command->warn('Nenhum morador encontrado. Execute primeiro o CustomCondominiumResidentsSeeder.');
            return;
        }

        $adsData = [
            [
                'title' => 'Sofá 3 lugares usado',
                'description' => 'Sofá em bom estado, cor cinza, apenas alguns sinais de uso. Perfeito para sala pequena.',
                'category' => AdCategory::Furniture,
                'condition' => AdCondition::Good,
                'price' => 450.00,
                'contact_phone' => '(11) 99999-1111',
                'contact_email' => 'vendedor1@teste.com',
            ],
            [
                'title' => 'Geladeira Brastemp - Nova',
                'description' => 'Geladeira Brastemp 400L, duplex, frost free. Ainda na garantia, sem uso.',
                'category' => AdCategory::Electronics,
                'condition' => AdCondition::New,
                'price' => 1800.00,
                'contact_phone' => '(11) 99999-2222',
                'contact_email' => 'vendedor2@teste.com',
            ],
            [
                'title' => 'Bicicleta Infantil - Aro 16',
                'description' => 'Bicicleta rosa com rodinhas, para criança de 4 a 7 anos. Muito bem conservada.',
                'category' => AdCategory::Others,
                'condition' => AdCondition::LikeNew,
                'price' => 180.00,
                'contact_phone' => '(11) 99999-3333',
                'contact_email' => 'vendedor3@teste.com',
            ],
            [
                'title' => 'Livros Didáticos Fundamental II',
                'description' => 'Coleção completa de livros do 6º ao 9º ano. Todas as matérias incluídas.',
                'category' => AdCategory::Others,
                'condition' => AdCondition::Good,
                'price' => 250.00,
                'contact_phone' => '(11) 99999-4444',
                'contact_email' => 'vendedor4@teste.com',
            ],
            [
                'title' => 'Mesa de Jantar com 6 Cadeiras',
                'description' => 'Conjunto completo em madeira maciça. Mesa redonda com 1,20m de diâmetro.',
                'category' => AdCategory::Furniture,
                'condition' => AdCondition::Good,
                'price' => 800.00,
                'contact_phone' => '(11) 99999-5555',
                'contact_email' => 'vendedor5@teste.com',
            ],
            [
                'title' => 'Notebook Dell - Para Peças',
                'description' => 'Notebook com defeito na tela, mas demais componentes funcionando. Ideal para peças.',
                'category' => AdCategory::Electronics,
                'condition' => AdCondition::Fair,
                'price' => 200.00,
                'contact_phone' => '(11) 99999-6666',
                'contact_email' => 'vendedor6@teste.com',
            ],
            [
                'title' => 'Roupas Infantis - Lote Menino 2-4 anos',
                'description' => 'Lote com 15 peças: camisetas, bermudas e calças. Marcas diversas, bem conservadas.',
                'category' => AdCategory::Others,
                'condition' => AdCondition::Good,
                'price' => 120.00,
                'contact_phone' => '(11) 99999-7777',
                'contact_email' => 'vendedor7@teste.com',
            ],
            [
                'title' => 'Microondas Panasonic 32L',
                'description' => 'Microondas em perfeito funcionamento, pouco uso. Todos os acessórios inclusos.',
                'category' => AdCategory::Electronics,
                'condition' => AdCondition::LikeNew,
                'price' => 280.00,
                'contact_phone' => '(11) 99999-8888',
                'contact_email' => 'vendedor8@teste.com',
            ],
        ];

        foreach ($condominiums as $condominium) {
            $condoResidents = $residents->where('condominium_id', $condominium->id);
            
            if ($condoResidents->isEmpty()) {
                continue;
            }

            foreach ($adsData as $adData) {
                $resident = $condoResidents->random();
                
                // Buscar um usuário associado ao condomínio
                $user = \App\Models\User::where('condominium_id', $condominium->id)->first();
                if (!$user) {
                    // Se não encontrar usuário do condomínio, usar o primeiro usuário disponível
                    $user = \App\Models\User::first();
                }
                
                if (!$user) {
                    $this->command->warn('Nenhum usuário encontrado. Pulando criação de anúncios.');
                    continue;
                }
                
                Ad::firstOrCreate(
                    [
                        'title' => $adData['title'],
                        'resident_id' => $resident->id,
                        'condominium_id' => $condominium->id,
                    ],
                    array_merge($adData, [
                        'user_id' => $user->id,
                        'resident_id' => $resident->id,
                        'condominium_id' => $condominium->id,
                        'status' => AdStatus::Active,
                        'expires_at' => now()->addDays(30),
                        'created_at' => now()->subDays(rand(1, 10)),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $this->command->info('Anúncios classificados criados com sucesso para ' . $condominiums->count() . ' condomínio(s)!');
    }
} 