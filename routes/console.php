<?php

use App\Services\ApprovalWorkflowService;
use App\Services\RenewalService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('workflow:check-sla', function (ApprovalWorkflowService $workflow) {
    $count = $workflow->markSlaBreaches();
    $this->info("Escalated {$count} approval requests.");
})->purpose('Mark approval requests that breached SLA');

Artisan::command('renewals:schedule-reminders', function (RenewalService $renewalService) {
    $count = $renewalService->scheduleReminders();
    $this->info("Scheduled {$count} renewal reminders.");
})->purpose('Schedule renewal reminders for expiring policies');

Schedule::command('workflow:check-sla')->everyFiveMinutes();
Schedule::command('renewals:schedule-reminders')->dailyAt('08:00');
