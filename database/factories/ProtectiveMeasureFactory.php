<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\ProtectiveMeasure;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProtectiveMeasure>
 */
class ProtectiveMeasureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProtectiveMeasure::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', '+6 months');
        $endDate = $this->faker->optional(0.7)->dateTimeBetween($startDate, '+2 years');

        return [
            'condominium_id' => Condominium::factory(),
            'protected_resident_id' => User::factory(),
            'aggressor_visitor_id' => Visitor::factory(),
            'process_number' => $this->faker->optional(0.6)->numerify('####.##.######'),
            'measure_description' => $this->faker->randomElement([
                'Medida protetiva de distanciamento mínimo de 200 metros.',
                'Proibição de contato por qualquer meio de comunicação.',
                'Proibição de aproximação da residência e local de trabalho.',
                'Medida cautelar de proteção conforme Lei Maria da Penha.',
                'Ordem restritiva com monitoramento eletrônico.',
                'Proibição de frequentar as áreas comuns do condomínio.',
            ]),
            'document_url' => $this->faker->optional(0.4)->url(),
            'validity_start_date' => $startDate,
            'validity_end_date' => $endDate,
            'is_active' => $this->faker->boolean(80), // 80% de chance de estar ativa
        ];
    }

    /**
     * Indicate that the protective measure is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the protective measure is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the protective measure has no end date.
     */
    public function indefinite(): static
    {
        return $this->state(fn (array $attributes) => [
            'validity_end_date' => null,
        ]);
    }

    /**
     * Indicate that the protective measure has a document attached.
     */
    public function withDocument(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_url' => '/storage/protective_measures_docs/sample_document.pdf',
        ]);
    }

    /**
     * Indicate that the protective measure is for a specific condominium.
     */
    public function forCondominium(int $condominiumId): static
    {
        return $this->state(fn (array $attributes) => [
            'condominium_id' => $condominiumId,
        ]);
    }

    /**
     * Indicate that the protective measure is for specific resident and visitor.
     */
    public function between(int $protectedResidentId, int $aggressorVisitorId): static
    {
        return $this->state(fn (array $attributes) => [
            'protected_resident_id' => $protectedResidentId,
            'aggressor_visitor_id' => $aggressorVisitorId,
        ]);
    }

    /**
     * Indicate that the protective measure is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'validity_start_date' => Carbon::now()->subDays(rand(1, 30)),
            'validity_end_date' => Carbon::now()->addDays(rand(30, 365)),
        ]);
    }

    /**
     * Indicate that the protective measure is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'validity_start_date' => Carbon::now()->subDays(rand(365, 730)),
            'validity_end_date' => Carbon::now()->subDays(rand(1, 30)),
            'is_active' => false,
        ]);
    }
}
