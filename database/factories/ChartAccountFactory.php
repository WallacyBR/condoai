<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\ChartAccount;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChartAccount>
 */
class ChartAccountFactory extends Factory
{
    protected $model = ChartAccount::class;

    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'parent_id' => null,
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(AccountType::values()),
            'account_number' => $this->faker->optional()->numerify('####-##'),
        ];
    }
}
