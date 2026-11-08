<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'license_plate' => strtoupper($this->faker->bothify('???-####')),
            'model' => $this->faker->word(),
            'color' => $this->faker->safeColorName(),
            'apartment_id' => Apartment::factory(),
            'parking_spot_id' => null,
        ];
    }
}
