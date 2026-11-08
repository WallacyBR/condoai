<?php

namespace Database\Factories;

use App\Models\CommonAreaBooking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends Factory<CommonAreaBooking>
 */
class CommonAreaBookingFactory extends Factory
{
    protected $model = CommonAreaBooking::class;

    public function definition(): array
    {
        $start = Carbon::now()->addDay();
        return [
            'start_at' => $start,
            'end_at' => (clone $start)->addHour(),
        ];
    }
}
