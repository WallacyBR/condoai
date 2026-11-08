<?php

namespace Database\Factories;

use App\Models\BudgetItem;
use App\Models\ChartAccount;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetItemFactory extends Factory
{
    protected $model = BudgetItem::class;

    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'chart_account_id' => ChartAccount::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 5000),
            'month' => $this->faker->numberBetween(1, 12),
            'year' => $this->faker->numberBetween(2024, 2025),
        ];
    }
}
