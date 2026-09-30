<?php

namespace Database\Factories;

use App\Models\Block;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Block>
 */
class BlockFactory extends Factory
{
    protected $model = Block::class;

    public function definition(): array
    {
        return [
            'name' => 'Block '.$this->faker->unique()->randomLetter,
            'condominium_id' => Condominium::factory(),
            'tower_id' => null,
            'active' => true,
        ];
    }
}
