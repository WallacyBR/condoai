<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Payable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payable>
 */
class PayableFactory extends Factory
{
    protected $model = Payable::class;

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
            'paid_at' => null,
        ];
    }
}
