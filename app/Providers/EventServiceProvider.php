<?php

namespace App\Providers;

use App\Events\Access\AccessRequestEntryWasRecorded;
use App\Events\Access\AccessRequestExitWasRecorded;
use App\Events\Access\AccessRequestWasCreated;
use App\Events\Access\AccessRequestWasResponded;
use App\Events\CommonAreas\CommonAreaBookingWasApproved;
use App\Events\CommonAreas\CommonAreaBookingWasCancelled;
use App\Events\CommonAreas\CommonAreaBookingWasCreated;
use App\Events\Finance\ExpenseRequestWasApproved;
use App\Events\Maintenance\MaintenanceRecordWasCompleted;
use App\Events\Maintenance\MaintenanceRecordWasReopened;
use App\Events\Maintenance\MaintenanceScheduleWasAssigned;
use App\Events\Notifications\InboxSmokeRequested;
use App\Events\Notices\NoticeWasPublished;
use App\Events\Occurrences\OccurrenceWasCreated;
use App\Events\Packages\PackageWasReceived;
use App\Listeners\Notifications\SendAccessRequestCreatedNotification;
use App\Listeners\Notifications\SendAccessRequestEntryNotification;
use App\Listeners\Notifications\SendAccessRequestExitNotification;
use App\Listeners\Notifications\SendAccessRequestRespondedNotification;
use App\Listeners\Notifications\SendCommonAreaBookingApprovedNotification;
use App\Listeners\Notifications\SendCommonAreaBookingCancelledNotification;
use App\Listeners\Notifications\SendCommonAreaBookingCreatedNotification;
use App\Listeners\Notifications\SendExpenseRequestApprovedNotification;
use App\Listeners\Notifications\SendInboxSmokeNotification;
use App\Listeners\Notifications\SendMaintenanceRecordCompletedNotification;
use App\Listeners\Notifications\SendMaintenanceRecordReopenedNotification;
use App\Listeners\Notifications\SendMaintenanceScheduleAssignedNotification;
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
        AccessRequestWasCreated::class => [
            SendAccessRequestCreatedNotification::class,
        ],
        AccessRequestWasResponded::class => [
            SendAccessRequestRespondedNotification::class,
        ],
        AccessRequestEntryWasRecorded::class => [
            SendAccessRequestEntryNotification::class,
        ],
        AccessRequestExitWasRecorded::class => [
            SendAccessRequestExitNotification::class,
        ],
        CommonAreaBookingWasCreated::class => [
            SendCommonAreaBookingCreatedNotification::class,
        ],
        CommonAreaBookingWasApproved::class => [
            SendCommonAreaBookingApprovedNotification::class,
        ],
        CommonAreaBookingWasCancelled::class => [
            SendCommonAreaBookingCancelledNotification::class,
        ],
        MaintenanceScheduleWasAssigned::class => [
            SendMaintenanceScheduleAssignedNotification::class,
        ],
        MaintenanceRecordWasCompleted::class => [
            SendMaintenanceRecordCompletedNotification::class,
        ],
        MaintenanceRecordWasReopened::class => [
            SendMaintenanceRecordReopenedNotification::class,
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
