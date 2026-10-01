<?php

namespace Database\Factories;

use App\Enums\OccurrencePriority;
use App\Enums\OccurrenceStatus;
use App\Models\Condominium;
use App\Models\Occurrence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OccurrenceFactory extends Factory
{
    protected $model = Occurrence::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(OccurrenceStatus::values()),
            'priority' => $this->faker->randomElement(OccurrencePriority::values()),
            'user_id' => User::factory(),
            'condominium_id' => Condominium::factory(),
        ];
    }
}
