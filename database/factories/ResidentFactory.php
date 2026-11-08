<?php

namespace Database\Factories;

use App\Enums\ResidentClassification;
use App\Enums\PersonType;
use App\Models\Condominium;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resident>
 */
class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'rg' => $this->faker->numerify('#########'),
            'cpf' => $this->faker->numerify('###########'),
            'cell_phone' => $this->faker->phoneNumber,
            'commercial_phone' => $this->faker->phoneNumber,
            'whatsapp' => $this->faker->phoneNumber,
            'photo' => null,
            'person_type' => PersonType::FISICA,
            'notes' => $this->faker->sentence,
            'is_porter' => false,
            'is_janitor' => false,
            'is_cleaning' => false,
            'apartment_id' => null,
            'billing_address_type' => 'condominium',
            'cep' => $this->faker->postcode,
            'address' => $this->faker->streetAddress,
            'neighborhood' => $this->faker->citySuffix,
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr,
            'email' => $this->faker->unique()->safeEmail,
            'classification' => $this->faker->randomElement(ResidentClassification::cases()),
            'active' => $this->faker->boolean(90),
            'condominium_id' => Condominium::factory(),
        ];
    }

    /**
     * Indica que o residente é um proprietário
     */
    public function proprietario(): static
    {
        return $this->state(fn (array $attributes) => [
            'classification' => ResidentClassification::PROPRIETARIO,
        ]);
    }

    /**
     * Indica que o residente é um morador
     */
    public function morador(): static
    {
        return $this->state(fn (array $attributes) => [
            'classification' => ResidentClassification::MORADOR,
        ]);
    }

    /**
     * Indica que o residente é um inquilino
     */
    public function inquilino(): static
    {
        return $this->state(fn (array $attributes) => [
            'classification' => ResidentClassification::INQUILINO,
        ]);
    }
}
