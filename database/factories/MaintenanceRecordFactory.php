<?php

namespace Database\Factories;

use App\Models\MaintenanceRecord;
use App\Models\Condominium;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class MaintenanceRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MaintenanceRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'completed', 'overdue', 'under_review', 'reopened', 'archived']);
        $performedAt = null;
        $reviewedAt = null;
        $reviewedBy = null;

        if ($status === 'completed' || $status === 'archived' || $status === 'under_review') {
            $performedAt = Carbon::instance($this->faker->dateTimeBetween('-1 week', 'now'));
            if ($status === 'under_review' || ($status === 'completed' && $this->faker->boolean(30))) {
                $reviewedAt = $performedAt->copy()->addDays($this->faker->numberBetween(1, 3));
                $reviewedBy = User::factory();
            }
        } elseif ($status === 'overdue') {
             $performedAt = null; // Overdue means not performed
        }


        return [
            'notes' => $this->faker->paragraph,
            'status' => $status,
            'performed_at' => $performedAt,
            'reviewed_at' => $reviewedAt,
            'condominium_id' => Condominium::factory(),
            'maintenance_schedule_id' => MaintenanceSchedule::factory(),
            'user_id' => User::factory(), // User who performed or is assigned
            'reviewed_by_user_id' => $reviewedBy,
        ];
    }
}
