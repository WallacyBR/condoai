<?php

namespace Database\Factories;

use App\Models\Block;
use App\Models\Condominium;
use App\Models\Tower;
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
            'name' => 'Block ' . $this->faker->unique()->randomLetter,
            'active' => $this->faker->boolean(90),
        ];
    }
}
