<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\ParkingSpot;
use Illuminate\Database\Seeder;

class ParkingSpotSeeder extends Seeder
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

        foreach ($condominiums as $condominium) {
            $apartments = Apartment::where('condominium_id', $condominium->id)->get();

            if ($apartments->isEmpty()) {
                continue;
            }

            // Criar vagas para cada apartamento (1 vaga por apartamento)
            foreach ($apartments as $apartment) {
                ParkingSpot::firstOrCreate(
                    [
                        'identifier' => sprintf('G-%s', $apartment->number),
                        'condominium_id' => $condominium->id,
                    ],
                    [
                        'apartment_id' => $apartment->id,
                        'condominium_id' => $condominium->id,
                    ]
                );
            }

            // Criar algumas vagas extras para locação
            $extraSpots = min(10, $apartments->count());
            for ($i = 1; $i <= $extraSpots; $i++) {
                $randomApartment = $apartments->random();
                $rentedTo = rand(0, 1) ? $apartments->random() : null;

                ParkingSpot::firstOrCreate(
                    [
                        'identifier' => sprintf('EXTRA-%03d', $i),
                        'condominium_id' => $condominium->id,
                    ],
                    [
                        'apartment_id' => $randomApartment->id,
                        'rented_to_apartment_id' => $rentedTo?->id,
                        'condominium_id' => $condominium->id,
                    ]
                );
            }
        }

        $this->command->info('Vagas de estacionamento criadas com sucesso para '.$condominiums->count().' condomínio(s)!');
    }
}
