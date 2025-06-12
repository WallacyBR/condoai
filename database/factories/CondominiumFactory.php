<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Condominium>
 */
class CondominiumFactory extends Factory
{
    protected $model = \App\Models\Condominium::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'document' => $this->faker->unique()->numerify('###########'),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'responsible' => $this->faker->name(),
            'domain' => $this->faker->unique()->domainName(),
            'active' => true,
        ];
    }
}
