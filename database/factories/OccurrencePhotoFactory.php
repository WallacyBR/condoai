<?php

namespace Database\Factories;

use App\Models\Occurrence;
use App\Models\OccurrencePhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

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
