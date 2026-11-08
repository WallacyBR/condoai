<?php

namespace App\Events\Finance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseRequestWasApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $condominiumId,
        public readonly ?int $actorId,
        public readonly int $expenseRequestId,
    ) {}
}
