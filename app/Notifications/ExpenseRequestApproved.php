<?php

namespace App\Notifications;

use App\Models\ExpenseRequest;

class ExpenseRequestApproved extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $expenseRequestId,
        private readonly string $title,
        private readonly string $amount,
        private readonly ?string $approvedAt,
        private readonly ?int $actorId,
    ) {}

    protected function buildPayload(): array
    {
        $formattedAmount = 'R$ '.number_format((float) $this->amount, 2, ',', '.');

        return array_merge(
            $this->basePayload(
                $this->condominiumId,
                'finance',
                'Despesa aprovada',
                "{$this->title} — {$formattedAmount}",
                "/finance/expense-requests/{$this->expenseRequestId}",
                "expense_request.approved:{$this->expenseRequestId}",
                $this->actorId,
                ExpenseRequest::class,
                $this->expenseRequestId,
            ),
            [
                'amount' => $this->amount,
                'approved_at' => $this->approvedAt,
            ],
        );
    }
}
