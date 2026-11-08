<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\Resident;
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
            'owner_id' => Resident::factory(),
            'tenant_id' => null,
            'active' => $this->faker->boolean(90),
        ];
    }
}
