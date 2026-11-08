<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Apartment>
 */
class ApartmentFactory extends Factory
{
    protected $model = Apartment::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->numerify('##'),
            'floor' => $this->faker->numberBetween(1, 20),
            'condominium_id' => Condominium::factory(),
            'tower_id' => null,
            'block_id' => null,
            'owner_id' => null,
            'tenant_id' => null,
            'active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Apartment $apartment) {
            if ($apartment->block_id !== null) {
                return;
            }

            $condominiumId = $apartment->condominium_id;

            if ($condominiumId instanceof Condominium) {
                $condominiumId = $condominiumId->getKey();
            }

            $apartment->block_id = Block::factory()->create([
                'condominium_id' => $condominiumId,
            ])->id;
        });
    }
}
