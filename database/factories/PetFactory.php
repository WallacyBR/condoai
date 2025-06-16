<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\Apartment;
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
            'name' => $this->faker->firstName,
            'species' => $this->faker->randomElement(['Cão', 'Gato', 'Pássaro']),
            'breed' => $this->faker->word,
            'photo_path' => null,
            'apartment_id' => Apartment::factory(),
        ];
    }
}
