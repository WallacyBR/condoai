<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\MaintenanceArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceAreaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MaintenanceArea::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true), // Ex: "Electrical System Area"
            'description' => $this->faker->sentence,
            'condominium_id' => Condominium::factory(), // Cria um Condominium se não for passado
        ];
    }
}
