<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Apartment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'apartment_id' => Apartment::factory(),
            'photo' => $this->faker->imageUrl(640, 480, 'packages'),
            'code' => strtoupper(substr(md5(uniqid((string) random_int(1, 9999), true)), 0, 8)),
            'retrieved_at' => null,
        ];
    }
}
