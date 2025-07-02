<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\Resident;
use App\Models\Condominium;
use App\Models\Apartment;

class PackageSeeder extends Seeder
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

        $couriers = [
            'Correios',
            'Mercado Livre',
            'Amazon',
            'Shopee',
            'Magazine Luiza',
            'Americanas',
            'Casas Bahia',
            'Extra',
            'Drogasil',
            'Farmácia Popular',
        ];

        $packageTypes = [
            'Encomenda',
            'Documento',
            'Medicamento',
            'Alimentação',
        ];

        foreach ($condominiums as $condominium) {
            $condoResidents = $residents->where('condominium_id', $condominium->id);
            $apartments = Apartment::where('condominium_id', $condominium->id)->get();
            
            if ($condoResidents->isEmpty() || $apartments->isEmpty()) {
                continue;
            }

            // Criar pacotes para os últimos 30 dias
            for ($i = 0; $i < 25; $i++) {
                $resident = $condoResidents->random();
                $apartment = $apartments->random();
                $courier = $couriers[array_rand($couriers)];
                $packageType = $packageTypes[array_rand($packageTypes)];
                
                $arrivedAt = now()->subDays(rand(0, 30));
                $isDelivered = rand(0, 1);
                $deliveredAt = $isDelivered ? $arrivedAt->copy()->addHours(rand(1, 48)) : null;
                
                Package::create([
                    'recipient_name' => $resident->name,
                    'recipient_apartment' => $apartment->number,
                    'recipient_phone' => $resident->cell_phone,
                    'sender' => $courier,
                    'description' => $packageType . ' - ' . $courier,
                    'size' => ['pequeno', 'médio', 'grande'][rand(0, 2)],
                    'weight' => rand(1, 50) / 10, // peso em kg
                    'tracking_code' => strtoupper(substr(md5(rand()), 0, 10)),
                    'arrived_at' => $arrivedAt,
                    'delivered_at' => $deliveredAt,
                    'delivered_to' => $isDelivered ? ['Proprietário', 'Familiar', 'Porteiro'][rand(0, 2)] : null,
                    'is_delivered' => $isDelivered,
                    'notes' => $i % 5 === 0 ? 'Pacote frágil - cuidado no manuseio' : null,
                    'received_by' => 'Porteiro João',
                    'condominium_id' => $condominium->id,
                    'apartment_id' => $apartment->id,
                    'resident_id' => $resident->id,
                    'created_at' => $arrivedAt,
                    'updated_at' => $deliveredAt ?? $arrivedAt,
                ]);
            }
        }

        $this->command->info('Pacotes criados com sucesso para ' . $condominiums->count() . ' condomínio(s)!');
    }
} 