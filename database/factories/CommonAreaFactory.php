<?php

namespace Database\Factories;

use App\Models\CommonArea;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommonAreaFactory extends Factory
{
    protected $model = CommonArea::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word.' Hall',
            'description' => $this->faker->sentence,
            'condominium_id' => Condominium::factory(),
            'capacity' => $this->faker->numberBetween(10, 100),
        ];
    }
}
