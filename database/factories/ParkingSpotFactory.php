<?php

namespace Database\Factories;

use App\Models\ParkingSpot;
use App\Models\Condominium;
use App\Models\Apartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParkingSpot>
 */
class ParkingSpotFactory extends Factory
{
    protected $model = ParkingSpot::class;

    public function definition(): array
    {
        return [
            'identifier' => $this->faker->unique()->bothify('V-###'),
            'condominium_id' => Condominium::factory(),
            'apartment_id' => Apartment::factory(),
            'rented_to_apartment_id' => null,
        ];
    }
}
