<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\Tower;
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
            'name' => 'Tower '.$this->faker->unique()->randomLetter,
            'active' => true,
            'condominium_id' => Condominium::factory(),
        ];
    }
}
