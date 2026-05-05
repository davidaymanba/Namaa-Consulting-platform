<?php

use App\Services\ApprovalWorkflowService;
use App\Services\PaymentCollectionService;
use App\Services\RenewalService;
use App\Services\ReinsuranceService;
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

Artisan::command('collections:schedule-reminders', function (PaymentCollectionService $service) {
    $count = $service->scheduleReminders();
    $this->info("Scheduled {$count} payment reminders.");
})->purpose('Schedule payment reminders for overdue installments');

Artisan::command('reinsurance:generate-bordereaux', function (ReinsuranceService $service) {
    $batch = $service->generateBordereauxBatch();
    $this->info("Generated bordereaux batch #{$batch->id}.");
})->purpose('Generate reinsurance bordereaux batch');

Schedule::command('workflow:check-sla')->everyFiveMinutes();
Schedule::command('renewals:schedule-reminders')->dailyAt('08:00');
Schedule::command('collections:schedule-reminders')->dailyAt('09:00');
Schedule::command('reinsurance:generate-bordereaux')->monthlyOn(1, '07:00');
