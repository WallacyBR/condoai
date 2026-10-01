<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\CommonArea;
use App\Models\CommonAreaBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommonAreaBookingFactory extends Factory
{
    protected $model = CommonAreaBooking::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 day', '+2 days');

        return [
            'common_area_id' => CommonArea::factory(),
            'apartment_id' => Apartment::factory(),
            'start_time' => $start,
            'end_time' => (clone $start)->modify('+2 hours'),
        ];
    }
}
