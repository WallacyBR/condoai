<?php

namespace Database\Factories;

use App\Models\Notice;
use App\Models\Condominium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    protected $model = Notice::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'image_path' => null,
            'condominium_id' => Condominium::factory(),
        ];
    }
}
