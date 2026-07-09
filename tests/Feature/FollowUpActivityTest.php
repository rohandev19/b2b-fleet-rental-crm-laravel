<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Models\ProspectContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_can_add_follow_up_activity(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create([
            'assigned_sales_id' => $sales->id,
            'status' => 'contacted',
        ]);
        $contact = ProspectContact::factory()->create(['prospect_id' => $prospect->id]);

        $response = $this->actingAs($sales)->post("/prospects/{$prospect->id}/follow-ups", [
            'contact_id' => $contact->id,
            'activity_type' => 'call',
            'activity_date' => now()->format('Y-m-d H:i:s'),
            'summary' => 'Called procurement team',
            'detail' => 'Discussed monthly fleet needs.',
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'outcome' => 'positive',
        ]);

        $response->assertRedirect(route('prospects.show', $prospect));

        $this->assertDatabaseHas('follow_up_activities', [
            'prospect_id' => $prospect->id,
            'contact_id' => $contact->id,
            'user_id' => $sales->id,
            'summary' => 'Called procurement team',
            'outcome' => 'positive',
        ]);

        $this->assertNotNull($prospect->fresh()->next_follow_up_at);
    }

    public function test_follow_up_cannot_be_added_to_lost_prospect(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create([
            'assigned_sales_id' => $sales->id,
            'status' => 'lost',
            'lost_reason' => 'No budget.',
        ]);

        $this->actingAs($sales)->post("/prospects/{$prospect->id}/follow-ups", [
            'activity_type' => 'call',
            'activity_date' => now()->format('Y-m-d H:i:s'),
            'summary' => 'Trying again',
        ])->assertSessionHasErrors('prospect_id');
    }

    public function test_contact_must_belong_to_same_prospect(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create(['assigned_sales_id' => $sales->id]);
        $otherContact = ProspectContact::factory()->create();

        $this->actingAs($sales)->post("/prospects/{$prospect->id}/follow-ups", [
            'contact_id' => $otherContact->id,
            'activity_type' => 'email',
            'activity_date' => now()->format('Y-m-d H:i:s'),
            'summary' => 'Sent deck',
        ])->assertSessionHasErrors('contact_id');
    }

    public function test_today_follow_up_page_lists_todays_reminders(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $activity = FollowUpActivity::factory()->create([
            'next_follow_up_at' => now()->setTime(10, 0),
            'summary' => 'Today reminder',
        ]);

        $this->actingAs($manager)
            ->get('/follow-ups/today')
            ->assertOk()
            ->assertSee('Today reminder')
            ->assertSee($activity->prospect->company_name);
    }

    public function test_overdue_follow_up_page_lists_overdue_reminders(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        FollowUpActivity::factory()->create([
            'next_follow_up_at' => now()->subDay()->setTime(10, 0),
            'summary' => 'Overdue reminder',
        ]);

        $this->actingAs($manager)
            ->get('/follow-ups/overdue')
            ->assertOk()
            ->assertSee('Overdue reminder');
    }

    public function test_finance_cannot_access_follow_up_pages(): void
    {
        $finance = User::factory()->create(['role' => UserRole::Finance]);

        $this->actingAs($finance)
            ->get('/follow-ups/today')
            ->assertForbidden();
    }

    public function test_activity_owner_can_update_follow_up(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $activity = FollowUpActivity::factory()->create(['user_id' => $sales->id]);

        $response = $this->actingAs($sales)->put("/prospects/{$activity->prospect_id}/follow-ups/{$activity->id}", [
            'activity_type' => 'meeting',
            'activity_date' => now()->format('Y-m-d H:i:s'),
            'summary' => 'Updated meeting summary',
            'outcome' => 'neutral',
        ]);

        $response->assertRedirect(route('prospects.show', $activity->prospect));

        $this->assertDatabaseHas('follow_up_activities', [
            'id' => $activity->id,
            'summary' => 'Updated meeting summary',
            'activity_type' => 'meeting',
        ]);
    }

    public function test_other_sales_cannot_update_follow_up(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Sales]);
        $otherSales = User::factory()->create(['role' => UserRole::Sales]);
        $activity = FollowUpActivity::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherSales)->put("/prospects/{$activity->prospect_id}/follow-ups/{$activity->id}", [
            'activity_type' => 'meeting',
            'activity_date' => now()->format('Y-m-d H:i:s'),
            'summary' => 'Hijack summary',
        ])->assertForbidden();
    }
}
