<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'chart_account_id' => ChartAccount::factory(),
            'condominium_id' => Condominium::factory(),
            'created_by' => User::factory(),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'type' => $this->faker->randomElement(TransactionType::values()),
            'status' => 'pendente',
            'description' => $this->faker->sentence(),
        ];
    }
}
