<?php

namespace Database\Factories;

use App\Models\Notice;
use App\Models\NoticeAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NoticeAttachment>
 */
class NoticeAttachmentFactory extends Factory
{
    protected $model = NoticeAttachment::class;

    public function definition(): array
    {
        return [
            'notice_id' => Notice::factory(),
            'file_path' => 'notices/'.$this->faker->uuid.'.pdf',
            'original_name' => $this->faker->word().'.pdf',
            'file_type' => 'document',
            'mime_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(1000, 5000),
        ];
    }
}

