<?php

namespace App\Notifications;

use App\Models\ExpenseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpenseRequestApproved extends Notification
{
    use Queueable;

    public function __construct(private ExpenseRequest $expenseRequest) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'expense_request_id' => $this->expenseRequest->id,
            'amount' => $this->expenseRequest->amount,
            'approved_at' => $this->expenseRequest->approved_at,
        ];
    }
}
