<?php

namespace Database\Factories;

use App\Models\Tower;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tower>
 */
class TowerFactory extends Factory
{
    protected $model = Tower::class;

    public function definition(): array
    {
        return [
            'name' => 'Tower ' . $this->faker->unique()->randomLetter,
            'active' => $this->faker->boolean(90),
            'condominium_id' => Condominium::factory(),
        ];
    }
}
