<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pet>
 */
class PetFactory extends Factory
{
    protected $model = Pet::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'species' => $this->faker->randomElement(['Cachorro', 'Gato', 'Ave']),
            'breed' => $this->faker->word(),
            'photo' => $this->faker->imageUrl(640, 480, 'pets'),
            'apartment_id' => Apartment::factory(),
        ];
    }
}
