<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bank>
 */
class BankFactory extends Factory
{
    protected $model = Bank::class;

    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'name' => $this->faker->company.' Bank',
            'agency' => $this->faker->numerify('####'),
            'account_number' => $this->faker->numerify('########'),
            'balance' => $this->faker->randomFloat(2, 1000, 100000),
        ];
    }
}
