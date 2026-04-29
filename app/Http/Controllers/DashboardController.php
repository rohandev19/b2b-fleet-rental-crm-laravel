<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $role = auth()->user()->role;

        return view('dashboard', [
            'role' => $role,
            'metrics' => $this->metricsFor($role),
            'workQueue' => $this->workQueueFor($role),
        ]);
    }

    /**
     * @return list<array{label: string, value: string, trend: string, tone: string}>
     */
    private function metricsFor(UserRole $role): array
    {
        return match ($role) {
            UserRole::Admin => [
                ['label' => 'Users', 'value' => '4', 'trend' => 'Demo roles ready', 'tone' => 'sky'],
                ['label' => 'Active prospects', 'value' => '0', 'trend' => 'Prospect module next', 'tone' => 'emerald'],
                ['label' => 'Open quotations', 'value' => '0', 'trend' => 'Quotation module planned', 'tone' => 'amber'],
                ['label' => 'Audit entries', 'value' => '0', 'trend' => 'Audit module planned', 'tone' => 'slate'],
            ],
            UserRole::Sales => [
                ['label' => 'My prospects', 'value' => '0', 'trend' => 'Start from prospect module', 'tone' => 'sky'],
                ['label' => 'Follow-ups today', 'value' => '0', 'trend' => 'No pending reminders', 'tone' => 'emerald'],
                ['label' => 'Draft quotations', 'value' => '0', 'trend' => 'No drafts yet', 'tone' => 'amber'],
                ['label' => 'Won this month', 'value' => '0', 'trend' => 'Pipeline not started', 'tone' => 'slate'],
            ],
            UserRole::Manager => [
                ['label' => 'Pipeline value', 'value' => 'Rp0', 'trend' => 'No active pipeline', 'tone' => 'sky'],
                ['label' => 'Waiting approval', 'value' => '0', 'trend' => 'Approval module planned', 'tone' => 'amber'],
                ['label' => 'Conversion rate', 'value' => '0%', 'trend' => 'Needs CRM data', 'tone' => 'emerald'],
                ['label' => 'High priority', 'value' => '0', 'trend' => 'No prospects yet', 'tone' => 'slate'],
            ],
            UserRole::Finance => [
                ['label' => 'Approved quotations', 'value' => '0', 'trend' => 'No approved deals', 'tone' => 'emerald'],
                ['label' => 'Contract value', 'value' => 'Rp0', 'trend' => 'No finance data', 'tone' => 'sky'],
                ['label' => 'Ready for contract', 'value' => '0', 'trend' => 'No accepted quotes', 'tone' => 'amber'],
                ['label' => 'This month', 'value' => '0', 'trend' => 'No quotation data', 'tone' => 'slate'],
            ],
        };
    }

    /**
     * @return list<array{title: string, status: string}>
     */
    private function workQueueFor(UserRole $role): array
    {
        return match ($role) {
            UserRole::Admin => [
                ['title' => 'Review user access structure', 'status' => 'Ready'],
                ['title' => 'Prepare master data modules', 'status' => 'Next'],
                ['title' => 'Audit log design', 'status' => 'Planned'],
            ],
            UserRole::Sales => [
                ['title' => 'Add first company prospect', 'status' => 'Next'],
                ['title' => 'Capture primary PIC', 'status' => 'Planned'],
                ['title' => 'Log first follow-up', 'status' => 'Planned'],
            ],
            UserRole::Manager => [
                ['title' => 'Monitor new sales pipeline', 'status' => 'Next'],
                ['title' => 'Review submitted quotations', 'status' => 'Planned'],
                ['title' => 'Track sales performance', 'status' => 'Planned'],
            ],
            UserRole::Finance => [
                ['title' => 'Review approved quotation list', 'status' => 'Planned'],
                ['title' => 'Estimate contract value', 'status' => 'Planned'],
                ['title' => 'Prepare customer handoff', 'status' => 'Planned'],
            ],
        };
    }
}
