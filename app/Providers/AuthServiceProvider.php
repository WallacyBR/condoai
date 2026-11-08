<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Occurrence;
use App\Policies\OccurrencePolicy;
use App\Models\OccurrenceComment;
use App\Policies\OccurrenceCommentPolicy;
use App\Models\Payable;
use App\Models\Receivable;
use App\Policies\PayablePolicy;
use App\Policies\ReceivablePolicy;

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
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
