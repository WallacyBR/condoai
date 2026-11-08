<?php

namespace App\Providers;

use App\Events\Finance\ExpenseRequestWasApproved;
use App\Events\Notifications\InboxSmokeRequested;
use App\Events\Notices\NoticeWasPublished;
use App\Events\Occurrences\OccurrenceWasCreated;
use App\Events\Packages\PackageWasReceived;
use App\Listeners\Notifications\SendExpenseRequestApprovedNotification;
use App\Listeners\Notifications\SendInboxSmokeNotification;
use App\Listeners\Notifications\SendNoticePublishedNotification;
use App\Listeners\Notifications\SendOccurrenceCreatedNotification;
use App\Listeners\Notifications\SendPackageReceivedNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        InboxSmokeRequested::class => [
            SendInboxSmokeNotification::class,
        ],
        ExpenseRequestWasApproved::class => [
            SendExpenseRequestApprovedNotification::class,
        ],
        PackageWasReceived::class => [
            SendPackageReceivedNotification::class,
        ],
        NoticeWasPublished::class => [
            SendNoticePublishedNotification::class,
        ],
        OccurrenceWasCreated::class => [
            SendOccurrenceCreatedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
