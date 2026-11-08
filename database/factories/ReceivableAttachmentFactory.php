<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\Receivable;
use App\Models\ReceivableAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReceivableAttachment>
 */
class ReceivableAttachmentFactory extends Factory
{
    protected $model = ReceivableAttachment::class;

    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'receivable_id' => Receivable::factory(),
            'file_path' => 'receivables/'.$this->faker->uuid.'.pdf',
            'original_name' => $this->faker->word().'.pdf',
            'file_type' => 'document',
            'mime_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(1000, 5000),
        ];
    }
}
