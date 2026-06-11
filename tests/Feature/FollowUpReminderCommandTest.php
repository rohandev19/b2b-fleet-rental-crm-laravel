<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FollowUpReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_follow_up_reminder_command_lists_overdue_and_today_reminders(): void
    {
        Carbon::setTestNow('2026-07-10 08:00:00');

        $sales = User::factory()->create([
            'name' => 'Rohan Sales',
            'role' => UserRole::Sales,
        ]);
        $prospect = Prospect::factory()->create([
            'company_name' => 'PT Reminder Due',
            'assigned_sales_id' => $sales->id,
        ]);

        FollowUpActivity::factory()->create([
            'prospect_id' => $prospect->id,
            'user_id' => $sales->id,
            'summary' => 'Overdue renewal call',
            'next_follow_up_at' => '2026-07-09 10:00:00',
        ]);

        FollowUpActivity::factory()->create([
            'prospect_id' => $prospect->id,
            'user_id' => $sales->id,
            'summary' => 'Today pricing check',
            'next_follow_up_at' => '2026-07-10 15:00:00',
        ]);

        FollowUpActivity::factory()->create([
            'prospect_id' => $prospect->id,
            'user_id' => $sales->id,
            'summary' => 'Future site visit',
            'next_follow_up_at' => '2026-07-11 09:00:00',
        ]);

        $this->artisan('crm:follow-up-reminders')
            ->expectsOutput('Follow-up reminders: 1 overdue, 1 due today.')
            ->expectsTable(
                ['Due At', 'Status', 'Prospect', 'Sales', 'Summary'],
                [
                    ['2026-07-09 10:00', 'Overdue', 'PT Reminder Due', 'Rohan Sales', 'Overdue renewal call'],
                    ['2026-07-10 15:00', 'Today', 'PT Reminder Due', 'Rohan Sales', 'Today pricing check'],
                ],
            )
            ->assertExitCode(0);
    }

    public function test_follow_up_reminder_command_can_filter_today_only(): void
    {
        Carbon::setTestNow('2026-07-10 08:00:00');

        $sales = User::factory()->create([
            'name' => 'Nadia Sales',
            'role' => UserRole::Sales,
        ]);
        $prospect = Prospect::factory()->create([
            'company_name' => 'PT Today Reminder',
            'assigned_sales_id' => $sales->id,
        ]);

        FollowUpActivity::factory()->create([
            'prospect_id' => $prospect->id,
            'user_id' => $sales->id,
            'summary' => 'Old reminder',
            'next_follow_up_at' => '2026-07-09 10:00:00',
        ]);

        FollowUpActivity::factory()->create([
            'prospect_id' => $prospect->id,
            'user_id' => $sales->id,
            'summary' => 'Today reminder',
            'next_follow_up_at' => '2026-07-10 11:00:00',
        ]);

        $this->artisan('crm:follow-up-reminders --today')
            ->expectsOutput('Follow-up reminders: 0 overdue, 1 due today.')
            ->expectsTable(
                ['Due At', 'Status', 'Prospect', 'Sales', 'Summary'],
                [
                    ['2026-07-10 11:00', 'Today', 'PT Today Reminder', 'Nadia Sales', 'Today reminder'],
                ],
            )
            ->assertExitCode(0);
    }

    public function test_follow_up_reminder_command_reports_empty_state(): void
    {
        Carbon::setTestNow('2026-07-10 08:00:00');

        $this->artisan('crm:follow-up-reminders')
            ->expectsOutput('No due follow-up reminders.')
            ->assertExitCode(0);
    }
}
