<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MaintenanceEquipmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MaintenanceEquipment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true).' '.$this->faker->randomElement(['Pump', 'Generator', 'Elevator', 'Filter']),
            'description' => $this->faker->sentence,
            'location_details' => $this->faker->word.' Room - '.$this->faker->buildingNumber,
            'qr_code_hash' => Str::random(32),
            'qr_code_access_level' => $this->faker->randomElement(['public', 'internal_users', 'specific_users']),
            'condominium_id' => Condominium::factory(),
            'maintenance_area_id' => MaintenanceArea::factory(),
        ];
    }
}
