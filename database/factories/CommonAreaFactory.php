<?php

namespace Database\Factories;

use App\Models\CommonArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommonArea>
 */
class CommonAreaFactory extends Factory
{
    protected $model = CommonArea::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence,
            'rules' => $this->faker->sentence,
            'photo_path' => null,
            'active' => $this->faker->boolean(90),
        ];
    }
}
