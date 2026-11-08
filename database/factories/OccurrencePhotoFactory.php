<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OccurrencePhoto;
use App\Models\Occurrence;

class OccurrencePhotoFactory extends Factory
{
    protected $model = OccurrencePhoto::class;

    public function definition()
    {
        return [
            'occurrence_id' => Occurrence::factory(),
            'photo_path' => $this->faker->imageUrl(),
            'description' => $this->faker->sentence,
        ];
    }
}
