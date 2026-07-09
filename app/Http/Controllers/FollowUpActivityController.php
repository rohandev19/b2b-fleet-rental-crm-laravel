<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\FollowUp\StoreFollowUpActivityRequest;
use App\Http\Requests\FollowUp\UpdateFollowUpActivityRequest;
use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FollowUpActivityController extends Controller
{
    public function today(Request $request): View
    {
        $activities = $this->reminderQuery($request)
            ->whereDate('next_follow_up_at', today())
            ->paginate(10)
            ->withQueryString();

        return view('follow-ups.index', [
            'activities' => $activities,
            'title' => 'Today Follow-ups',
            'description' => 'Follow-up reminders scheduled for today.',
            'emptyMessage' => 'No follow-ups scheduled for today.',
        ]);
    }

    public function overdue(Request $request): View
    {
        $activities = $this->reminderQuery($request)
            ->whereDate('next_follow_up_at', '<', today())
            ->paginate(10)
            ->withQueryString();

        return view('follow-ups.index', [
            'activities' => $activities,
            'title' => 'Overdue Follow-ups',
            'description' => 'Follow-up reminders that need attention.',
            'emptyMessage' => 'No overdue follow-ups.',
        ]);
    }

    public function store(StoreFollowUpActivityRequest $request, Prospect $prospect, AuditLogger $auditLogger): RedirectResponse
    {
        $activity = $prospect->followUpActivities()->create($request->safe()->merge([
            'contact_id' => $request->input('contact_id') ?: null,
            'user_id' => $request->user()->id,
        ])->all());

        $this->syncProspectNextFollowUp($prospect, $activity);

        $auditLogger->log(
            'follow_up.created',
            "Created follow-up for {$prospect->company_name}.",
            $activity,
            null,
            $activity->only(['prospect_id', 'user_id', 'activity_type', 'activity_date', 'next_follow_up_at', 'outcome']),
            $request,
        );

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Follow-up activity added successfully.');
    }

    public function edit(Prospect $prospect, FollowUpActivity $followUpActivity): View
    {
        $this->authorizeActivityMutation($followUpActivity);

        $prospect->load('contacts');

        return view('follow-ups.edit', [
            'prospect' => $prospect,
            'activity' => $followUpActivity,
        ]);
    }

    public function update(UpdateFollowUpActivityRequest $request, Prospect $prospect, FollowUpActivity $followUpActivity, AuditLogger $auditLogger): RedirectResponse
    {
        $before = $followUpActivity->only(['activity_type', 'activity_date', 'summary', 'next_follow_up_at', 'outcome']);

        $followUpActivity->update($request->safe()->merge([
            'contact_id' => $request->input('contact_id') ?: null,
        ])->all());

        $this->syncProspectNextFollowUp($prospect, $followUpActivity);

        $auditLogger->log(
            'follow_up.updated',
            "Updated follow-up for {$prospect->company_name}.",
            $followUpActivity,
            $before,
            $followUpActivity->fresh()->only(['activity_type', 'activity_date', 'summary', 'next_follow_up_at', 'outcome']),
            $request,
        );

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Follow-up activity updated successfully.');
    }

    public function destroy(Prospect $prospect, FollowUpActivity $followUpActivity, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeActivityMutation($followUpActivity);

        $before = $followUpActivity->only(['prospect_id', 'user_id', 'activity_type', 'activity_date', 'summary']);

        $followUpActivity->delete();

        $auditLogger->log(
            'follow_up.deleted',
            "Deleted follow-up for {$prospect->company_name}.",
            $followUpActivity,
            $before,
            null,
        );

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Follow-up activity deleted successfully.');
    }

    private function reminderQuery(Request $request)
    {
        return FollowUpActivity::query()
            ->with(['prospect.assignedSales', 'contact', 'user'])
            ->whereNotNull('next_follow_up_at')
            ->when($request->user()->hasRole(UserRole::Sales), fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($request->filled('type'), fn ($query) => $query->where('activity_type', $request->string('type')))
            ->orderBy('next_follow_up_at');
    }

    private function authorizeActivityMutation(FollowUpActivity $activity): void
    {
        abort_unless(
            auth()->user()?->hasRole(UserRole::Admin) || auth()->id() === $activity->user_id,
            403,
        );
    }

    private function syncProspectNextFollowUp(Prospect $prospect, FollowUpActivity $activity): void
    {
        if ($activity->next_follow_up_at) {
            $prospect->update([
                'next_follow_up_at' => $activity->next_follow_up_at,
            ]);
        }
    }
}
