<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\Payable;
use App\Models\PayableAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayableAttachment>
 */
class PayableAttachmentFactory extends Factory
{
    protected $model = PayableAttachment::class;

    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'payable_id' => Payable::factory(),
            'file_path' => 'payables/'.$this->faker->uuid.'.pdf',
            'original_name' => $this->faker->word().'.pdf',
            'file_type' => 'document',
            'mime_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(1000, 5000),
        ];
    }
}
