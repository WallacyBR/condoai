<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OccurrenceComment;
use App\Models\Occurrence;
use App\Models\User;

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
