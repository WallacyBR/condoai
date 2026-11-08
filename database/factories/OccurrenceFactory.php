<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Occurrence;
use App\Models\User;
use App\Models\Condominium;
use App\Enums\OccurrenceStatus;
use App\Enums\OccurrencePriority;

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
