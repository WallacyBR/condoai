<?php

namespace Database\Factories;


use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Condominium>
 */
class CondominiumFactory extends Factory
{
    protected $model = Condominium::class;

    public function definition(): array
    {
        return [

            'name' => $this->faker->company . ' Condominium',
            'document' => $this->faker->unique()->numerify('##############'),
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr,
            'zip_code' => $this->faker->postcode,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'responsible' => $this->faker->name,
            'active' => $this->faker->boolean(90),
            'domain' => $this->faker->domainName,

        ];
    }
}
