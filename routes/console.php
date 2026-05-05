<?php

use App\Services\ApprovalWorkflowService;
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

Schedule::command('workflow:check-sla')->everyFiveMinutes();
