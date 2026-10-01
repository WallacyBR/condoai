<?php

namespace Database\Factories;

use App\Models\Occurrence;
use App\Models\OccurrenceComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OccurrenceCommentFactory extends Factory
{
    protected $model = OccurrenceComment::class;

    public function definition()
    {
        return [
            'occurrence_id' => Occurrence::factory(),
            'user_id' => User::factory(),
            'comment' => $this->faker->paragraph,
        ];
    }
}
