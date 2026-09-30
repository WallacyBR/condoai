<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Apartment;
use App\Models\User;

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
            'user_id' => User::factory(),
            'recipient_name' => fake()->name(),
            'recipient_apartment' => fake()->numerify('###'),
            'recipient_phone' => fake()->phoneNumber(),
            'sender' => fake()->company(),
            'description' => fake()->optional()->sentence(),
            'photo' => fake()->imageUrl(640, 480, 'packages'),
            'pickup_location' => fake()->word(),
            'observation' => fake()->sentence(),
            'signature' => fake()->optional()->imageUrl(100, 100, 'signature'),
            'document_photo' => fake()->optional()->imageUrl(640, 480, 'documents'),
            'code' => strtoupper(substr(md5(uniqid((string) random_int(1, 9999), true)), 0, 8)),
            'retrieved_at' => null,
        ];
    }
}
