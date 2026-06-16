<?php

use App\Models\FollowUpActivity;
use App\Models\Quotation;
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

Artisan::command('crm:expire-quotations {--dry-run : Preview expired quotations without updating them}', function (): int {
    $expirableStatuses = ['draft', 'submitted', 'approved', 'revised', 'sent'];
    $quotations = Quotation::query()
        ->with(['prospect:id,company_name'])
        ->whereIn('status', $expirableStatuses)
        ->whereDate('valid_until', '<', today())
        ->orderBy('valid_until')
        ->get();

    if ($quotations->isEmpty()) {
        $this->info('No quotations need to be expired.');

        return 0;
    }

    $this->info($quotations->count().' quotation(s) need to be expired.');
    $this->table(
        ['Quotation', 'Prospect', 'Current Status', 'Valid Until'],
        $quotations->map(fn (Quotation $quotation): array => [
            $quotation->quotation_number,
            $quotation->prospect?->company_name,
            str($quotation->status)->headline()->toString(),
            $quotation->valid_until?->toDateString(),
        ])->all(),
    );

    if ((bool) $this->option('dry-run')) {
        $this->comment('Dry run only. No quotations were updated.');

        return 0;
    }

    Quotation::query()
        ->whereKey($quotations->modelKeys())
        ->update(['status' => 'expired']);

    $this->info($quotations->count().' quotation(s) marked as expired.');

    return 0;
})->purpose('Mark quotations past their valid-until date as expired');

Schedule::command('crm:expire-quotations')->dailyAt('00:30');
