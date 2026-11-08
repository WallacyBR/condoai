<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\ParkingSpot;
use App\Models\Pet;
use App\Models\Resident;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehiclePetSeeder extends Seeder
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

        // Dados para veículos
        $carModels = ['Corolla', 'Civic', 'Onix', 'Ka', 'Gol', 'Uno', 'HB20', 'March', 'Siena', 'Sandero'];
        $carColors = ['Branco', 'Preto', 'Prata', 'Vermelho', 'Azul', 'Cinza'];

        // Dados para pets
        $dogBreeds = ['Labrador', 'Golden Retriever', 'Poodle', 'Bulldog', 'Beagle', 'Rottweiler', 'Pastor Alemão', 'SRD'];
        $catBreeds = ['Persian', 'Siamês', 'Maine Coon', 'British Shorthair', 'Ragdoll', 'SRD'];
        $petNames = ['Max', 'Bella', 'Charlie', 'Luna', 'Cooper', 'Lucy', 'Buddy', 'Molly', 'Rocky', 'Sadie'];

        foreach ($condominiums as $condominium) {
            $apartments = Apartment::where('condominium_id', $condominium->id)->get();
            $parkingSpots = ParkingSpot::where('condominium_id', $condominium->id)->get();

            foreach ($apartments as $apartment) {
                // 70% dos apartamentos têm pelo menos 1 veículo
                if (rand(1, 100) <= 70) {
                    $vehicleCount = rand(1, 2); // 1 ou 2 veículos por apartamento

                    for ($i = 0; $i < $vehicleCount; $i++) {
                        $model = $carModels[array_rand($carModels)];
                        $color = $carColors[array_rand($carColors)];
                        $parkingSpot = $parkingSpots->where('apartment_id', $apartment->id)->first();

                        Vehicle::create([
                            'model' => $model,
                            'color' => $color,
                            'license_plate' => $this->generateLicensePlate(),
                            'apartment_id' => $apartment->id,
                            'parking_spot_id' => $parkingSpot?->id,
                        ]);
                    }
                }
            }

            // Criar pets para os moradores
            $condoResidents = $residents->where('condominium_id', $condominium->id);

            // Verificar se há apartamentos no condomínio
            if ($apartments->isEmpty()) {
                $this->command->warn("Nenhum apartamento encontrado para o condomínio {$condominium->name}. Pulando criação de pets.");

                continue;
            }

            foreach ($condoResidents as $resident) {
                // 40% dos moradores têm pets
                if (rand(1, 100) <= 40) {
                    // Carregar os apartamentos do morador
                    $resident->load('apartments');

                    // Verificar se o morador tem apartamentos
                    if ($resident->apartments->isEmpty()) {
                        // Se não tem apartamentos, associar a um apartamento aleatório do condomínio
                        $randomApartment = $apartments->random();
                        $apartmentId = $randomApartment->id;
                    } else {
                        // Usar o primeiro apartamento do morador
                        $apartmentId = $resident->apartments->first()->id;
                    }

                    $petCount = rand(1, 3); // 1 a 3 pets por morador

                    for ($i = 0; $i < $petCount; $i++) {
                        $type = rand(0, 1) ? 'Cão' : 'Gato';
                        $breed = $type === 'Cão' ? $dogBreeds[array_rand($dogBreeds)] : $catBreeds[array_rand($catBreeds)];
                        $name = $petNames[array_rand($petNames)];

                        Pet::create([
                            'name' => $name,
                            'species' => $type,
                            'breed' => $breed,
                            'age' => rand(1, 15),
                            'weight' => $type === 'Cão' ? rand(5, 40) : rand(2, 8),
                            'color' => $carColors[array_rand($carColors)], // Reutilizando as cores
                            'is_vaccinated' => rand(0, 1),
                            'observations' => $i % 3 === 0 ? 'Pet muito dócil e sociável' : null,
                            'apartment_id' => $apartmentId,
                            'resident_id' => $resident->id,
                            'condominium_id' => $condominium->id,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Veículos e pets criados com sucesso para '.$condominiums->count().' condomínio(s)!');
    }

    private function generateLicensePlate(): string
    {
        // Formato antigo (ABC-1234) ou novo (ABC1D23)
        $useNewFormat = rand(0, 1);

        if ($useNewFormat) {
            // Formato novo: ABC1D23
            $letters = chr(rand(65, 90)).chr(rand(65, 90)).chr(rand(65, 90));
            $numbers = rand(0, 9).chr(rand(65, 90)).rand(10, 99);

            return $letters.$numbers;
        } else {
            // Formato antigo: ABC-1234
            $letters = chr(rand(65, 90)).chr(rand(65, 90)).chr(rand(65, 90));
            $numbers = rand(1000, 9999);

            return $letters.'-'.$numbers;
        }
    }
}
