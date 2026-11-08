<?php

namespace Database\Factories;

use App\Models\Visitor;
use App\Models\Apartment;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visitor>
 */
class VisitorFactory extends Factory
{
    protected $model = Visitor::class;

    public function definition(): array
    {
        $condo = Condominium::factory();
        return [
            'name' => $this->faker->name,
            'document' => $this->faker->numerify('###########'),
            'photo_path' => null,
            'condominium_id' => $condo,
            'apartment_id' => Apartment::factory()->for($condo),
            'visit_date' => $this->faker->dateTime,
        ];
    }
}
