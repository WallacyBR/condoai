<?php

namespace Database\Factories;

use App\Models\MaintenanceRecordPhoto;
use App\Models\Condominium;
use App\Models\MaintenanceRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceRecordPhotoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MaintenanceRecordPhoto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Placeholder for file_path. In a real seeding scenario, you might
        // copy some sample images to storage and use their paths.
        $fileName = $this->faker->word . '_' . $this->faker->randomNumber(5) . '.jpg';
        $filePath = 'maintenance_photos/' . $fileName;

        return [
            'file_path' => $filePath,
            'uploaded_at' => $this->faker->dateTimeThisMonth(),
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
        return $this->afterCreating(function (MaintenanceRecordPhoto $photo) {
            // If condominium_id is not set, try to get it from the record
            if (is_null($photo->condominium_id) && $photo->record) {
                $photo->condominium_id = $photo->record->condominium_id;
                $photo->save();
            }
        });
    }
}
