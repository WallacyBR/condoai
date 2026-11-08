<?php

namespace Database\Factories;

use App\Models\MaintenanceRecordSignature;
use App\Models\Condominium;
use App\Models\MaintenanceRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceRecordSignatureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MaintenanceRecordSignature::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = 'signature_' . $this->faker->unixTime() . '_' . $this->faker->randomNumber(5) . '.png';
        $filePath = 'maintenance_signatures/' . $fileName;

        return [
            'signature_path' => $filePath,
            'signed_at' => $this->faker->dateTimeThisMonth(),
            'condominium_id' => Condominium::factory(), // Or get from associated record
            'maintenance_record_id' => MaintenanceRecord::factory(),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterCreating(function (MaintenanceRecordSignature $signature) {
            // If condominium_id is not set, try to get it from the record
            if (is_null($signature->condominium_id) && $signature->record) {
                $signature->condominium_id = $signature->record->condominium_id;
                $signature->save();
            }
        });
    }
}
