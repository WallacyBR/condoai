<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Bank;
use App\Models\ChartAccount;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestAttachment;
use App\Models\Occurrence;
use App\Models\OccurrenceComment;
use App\Models\Payable;
use App\Models\Receivable;
use App\Policies\BankPolicy;
use App\Policies\ChartAccountPolicy;
use App\Policies\ExpenseRequestAttachmentPolicy;
use App\Policies\ExpenseRequestPolicy;
use App\Policies\OccurrenceCommentPolicy;
use App\Policies\OccurrencePolicy;
use App\Policies\PayablePolicy;
use App\Policies\ReceivablePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Occurrence::class => OccurrencePolicy::class,
        OccurrenceComment::class => OccurrenceCommentPolicy::class,
        Payable::class => PayablePolicy::class,
        Receivable::class => ReceivablePolicy::class,
        Bank::class => BankPolicy::class,
        ChartAccount::class => ChartAccountPolicy::class,
        ExpenseRequest::class => ExpenseRequestPolicy::class,
        ExpenseRequestAttachment::class => ExpenseRequestAttachmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
