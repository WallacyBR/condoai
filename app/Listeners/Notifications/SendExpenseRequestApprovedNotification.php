<?php

namespace App\Listeners\Notifications;

use App\Events\Finance\ExpenseRequestWasApproved;
use App\Models\ExpenseRequest;
use App\Notifications\ExpenseRequestApproved;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExpenseRequestApprovedNotification implements ShouldQueue
{
    use InteractsWithQueue;
    use RestoresTenantContext;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(ExpenseRequestWasApproved $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $expenseRequest = ExpenseRequest::query()->findOrFail($event->expenseRequestId);

            $users = $audienceResolver
                ->singleUser($event->condominiumId, (int) $expenseRequest->requested_by)
                ->reject(fn ($user) => $event->actorId !== null && (int) $user->id === (int) $event->actorId)
                ->unique('id');

            if ($users->isEmpty()) {
                return;
            }

            $approvedAt = $expenseRequest->approved_at?->toIso8601String();

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new ExpenseRequestApproved(
                    condominiumId: $event->condominiumId,
                    expenseRequestId: $expenseRequest->id,
                    title: $expenseRequest->title ?? 'Solicitação de Despesa',
                    amount: (string) $expenseRequest->amount,
                    approvedAt: $approvedAt,
                    actorId: $event->actorId,
                ));
            }
        });
    }
}
