<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpenseRequest>
 */
class ExpenseRequestFactory extends Factory
{
    protected $model = ExpenseRequest::class;

    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'requested_by' => User::factory(),
            'approved_by' => null,
            'amount' => $this->faker->randomFloat(2, 50, 2000),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'status' => 'pendente',
            'description' => $this->faker->sentence(),
            'approved_at' => null,
            'paid_at' => null,
        ];
    }
}
