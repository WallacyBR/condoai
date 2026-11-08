<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\Tower;
use App\Models\Resident;
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
            'owner_id' => Resident::factory(),
            'tenant_id' => null,
            'active' => $this->faker->boolean(90),
        ];
    }
}
