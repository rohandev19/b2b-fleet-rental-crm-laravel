<?php

use App\Models\FollowUpActivity;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('crm:follow-up-reminders {--today : Only show reminders due today} {--overdue : Only show overdue reminders}', function (): int {
    $today = today();
    $includeToday = (bool) $this->option('today');
    $includeOverdue = (bool) $this->option('overdue');

    if (! $includeToday && ! $includeOverdue) {
        $includeToday = true;
        $includeOverdue = true;
    }

    $activities = FollowUpActivity::query()
        ->with(['prospect:id,company_name,assigned_sales_id', 'user:id,name'])
        ->whereNotNull('next_follow_up_at')
        ->when($includeToday && $includeOverdue, fn ($query) => $query->whereDate('next_follow_up_at', '<=', $today))
        ->when($includeToday && ! $includeOverdue, fn ($query) => $query->whereDate('next_follow_up_at', $today))
        ->when($includeOverdue && ! $includeToday, fn ($query) => $query->whereDate('next_follow_up_at', '<', $today))
        ->orderBy('next_follow_up_at')
        ->get();

    if ($activities->isEmpty()) {
        $this->info('No due follow-up reminders.');

        return 0;
    }

    $overdueCount = $activities
        ->filter(fn (FollowUpActivity $activity): bool => $activity->next_follow_up_at->isBefore($today))
        ->count();
    $todayCount = $activities
        ->filter(fn (FollowUpActivity $activity): bool => $activity->next_follow_up_at->isSameDay($today))
        ->count();

    $this->info("Follow-up reminders: {$overdueCount} overdue, {$todayCount} due today.");
    $this->table(
        ['Due At', 'Status', 'Prospect', 'Sales', 'Summary'],
        $activities->map(fn (FollowUpActivity $activity): array => [
            $activity->next_follow_up_at->format('Y-m-d H:i'),
            $activity->next_follow_up_at->isBefore($today) ? 'Overdue' : 'Today',
            $activity->prospect?->company_name,
            $activity->user?->name,
            $activity->summary,
        ])->all(),
    );

    return 0;
})->purpose('Display due and overdue CRM follow-up reminders');

Schedule::command('crm:follow-up-reminders')->dailyAt('08:00');
