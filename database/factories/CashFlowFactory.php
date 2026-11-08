<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\CashFlow;
use App\Models\Condominium;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashFlow>
 */
class CashFlowFactory extends Factory
{
    protected $model = CashFlow::class;

    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'transaction_id' => Transaction::factory(),
            'date' => $this->faker->dateTimeThisMonth()->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement(TransactionType::values()),
            'description' => $this->faker->sentence(),
        ];
    }
}
