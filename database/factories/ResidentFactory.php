<?php

namespace Database\Factories;

use App\Models\Resident;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resident>
 */
class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'active' => $this->faker->boolean(90),
            'condominium_id' => Condominium::factory(),
        ];
    }
}
