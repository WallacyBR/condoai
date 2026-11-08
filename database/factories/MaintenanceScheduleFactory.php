<?php

namespace Database\Factories;

use App\Models\MaintenanceSchedule;
use App\Models\Condominium;
use App\Models\MaintenanceEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class MaintenanceScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MaintenanceSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['daily', 'weekly', 'biweekly', 'monthly', 'semiannual', 'annual', 'specific_days']);
        $isRecurring = $this->faker->boolean(80); // 80% chance of being recurring

        $specificDaysOfWeek = null;
        if ($type === 'weekly' || $type === 'specific_days') {
            $specificDaysOfWeek = $this->faker->randomElements([0, 1, 2, 3, 4, 5, 6], $this->faker->numberBetween(1, 3)); // Sun-Sat
        }

        $dayOfMonth = null;
        if ($type === 'monthly') {
            $dayOfMonth = $this->faker->numberBetween(1, 28);
        }

        $monthOfYear = null;
        if ($type === 'semiannual' || $type === 'annual') {
            $monthOfYear = $this->faker->numberBetween(1, 12);
        }

        $startDate = Carbon::instance($this->faker->dateTimeBetween('-1 month', '+1 month'));
        $endDate = null;
        if ($isRecurring && $this->faker->boolean(30)) { // 30% chance of recurring schedules having an end date
            $endDate = $startDate->copy()->addMonths($this->faker->numberBetween(3, 12));
        }


        return [
            'title' => 'Maintenance for ' . $this->faker->word,
            'description' => $this->faker->sentence,
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'due_time' => $this->faker->optional()->time('H:i'),
            'is_recurring' => $isRecurring,
            'specific_days_of_week' => $specificDaysOfWeek,
            'day_of_month' => $dayOfMonth,
            'month_of_year' => $monthOfYear,
            'condominium_id' => Condominium::factory(),
            'maintenance_equipment_id' => MaintenanceEquipment::factory(),
        ];
    }
}
