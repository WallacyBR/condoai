<?php

namespace Database\Factories;

use App\Models\Condominium;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpenseRequestAttachment>
 */
class ExpenseRequestAttachmentFactory extends Factory
{
    protected $model = ExpenseRequestAttachment::class;

    public function definition(): array
    {
        return [
            'condominium_id' => Condominium::factory(),
            'expense_request_id' => ExpenseRequest::factory(),
            'file_path' => 'expense_requests/'.$this->faker->uuid.'.pdf',
            'original_name' => $this->faker->word().'.pdf',
            'file_type' => 'document',
            'mime_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(1000, 5000),
        ];
    }
}
