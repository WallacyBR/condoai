<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Receivable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receivable>
 */
class ReceivableFactory extends Factory
{
    protected $model = Receivable::class;

    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'chart_account_id' => ChartAccount::factory(),
            'bank_id' => Bank::factory(),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'pendente',
            'description' => $this->faker->sentence(),
            'received_at' => null,
        ];
    }
}
