<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $role = $user->role;

        return view('dashboard', [
            'role' => $role,
            'metrics' => $this->metricsFor($role, $user->id),
            'pipelineSnapshot' => $this->pipelineSnapshot($role, $user->id),
            'workQueue' => $this->workQueueFor($role, $user->id),
            'recentQuotations' => $this->recentQuotations($role, $user->id),
            'overdueFollowUps' => $this->overdueFollowUps($role, $user->id),
        ]);
    }

    /**
     * @return list<array{label: string, value: string, trend: string, tone: string}>
     */
    private function metricsFor(UserRole $role, int $userId): array
    {
        $activeProspects = Prospect::query()->whereNotIn('status', ['won', 'lost'])->count();
        $openQuotationStatuses = ['draft', 'submitted', 'revised', 'sent'];
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        return match ($role) {
            UserRole::Admin => [
                ['label' => 'Users', 'value' => (string) User::query()->count(), 'trend' => 'All accounts', 'tone' => 'sky'],
                ['label' => 'Active prospects', 'value' => (string) $activeProspects, 'trend' => 'Open pipeline', 'tone' => 'emerald'],
                ['label' => 'Open quotations', 'value' => (string) Quotation::query()->whereIn('status', $openQuotationStatuses)->count(), 'trend' => 'Draft to sent', 'tone' => 'amber'],
                ['label' => 'Approved value', 'value' => $this->formatCurrency(Quotation::query()->where('status', 'approved')->sum('grand_total')), 'trend' => 'Ready for handoff', 'tone' => 'slate'],
            ],
            UserRole::Sales => [
                ['label' => 'My prospects', 'value' => (string) Prospect::query()->where('assigned_sales_id', $userId)->count(), 'trend' => 'Assigned accounts', 'tone' => 'sky'],
                ['label' => 'Follow-ups today', 'value' => (string) FollowUpActivity::query()->where('user_id', $userId)->whereDate('next_follow_up_at', today())->count(), 'trend' => 'Due today', 'tone' => 'emerald'],
                ['label' => 'Draft quotations', 'value' => (string) Quotation::query()->where('sales_id', $userId)->whereIn('status', ['draft', 'revised'])->count(), 'trend' => 'Needs submit', 'tone' => 'amber'],
                ['label' => 'Won this month', 'value' => (string) Prospect::query()->where('assigned_sales_id', $userId)->where('status', 'won')->whereBetween('updated_at', [$monthStart, $monthEnd])->count(), 'trend' => 'Closed prospects', 'tone' => 'slate'],
            ],
            UserRole::Manager => [
                ['label' => 'Pipeline value', 'value' => $this->formatCurrency(Quotation::query()->whereIn('status', ['submitted', 'approved', 'sent'])->sum('grand_total')), 'trend' => 'Submitted onward', 'tone' => 'sky'],
                ['label' => 'Waiting approval', 'value' => (string) Quotation::query()->where('status', 'submitted')->count(), 'trend' => 'Needs review', 'tone' => 'amber'],
                ['label' => 'Conversion rate', 'value' => $this->conversionRate(), 'trend' => 'Won vs total', 'tone' => 'emerald'],
                ['label' => 'High priority', 'value' => (string) Prospect::query()->where('priority', 'high')->whereNotIn('status', ['won', 'lost'])->count(), 'trend' => 'Active prospects', 'tone' => 'slate'],
            ],
            UserRole::Finance => [
                ['label' => 'Approved quotations', 'value' => (string) Quotation::query()->where('status', 'approved')->count(), 'trend' => 'Ready for finance', 'tone' => 'emerald'],
                ['label' => 'Contract value', 'value' => $this->formatCurrency(Quotation::query()->whereIn('status', ['approved', 'accepted'])->sum('grand_total')), 'trend' => 'Approved + accepted', 'tone' => 'sky'],
                ['label' => 'PDF ready', 'value' => (string) Quotation::query()->where('status', 'approved')->whereNotNull('pdf_path')->count(), 'trend' => 'Generated quotes', 'tone' => 'amber'],
                ['label' => 'This month', 'value' => $this->formatCurrency(Quotation::query()->where('status', 'approved')->whereBetween('quotation_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('grand_total')), 'trend' => 'Approved value', 'tone' => 'slate'],
            ],
        };
    }

    /**
     * @return list<array{title: string, status: string}>
     */
    private function workQueueFor(UserRole $role, int $userId): array
    {
        return match ($role) {
            UserRole::Admin => [
                ['title' => User::query()->where('is_active', false)->count().' inactive user accounts', 'status' => 'Access'],
                ['title' => Prospect::query()->where('status', 'new')->count().' new prospects need ownership', 'status' => 'CRM'],
                ['title' => Quotation::query()->where('status', 'approved')->whereNull('pdf_path')->count().' approved quotations without PDF', 'status' => 'PDF'],
            ],
            UserRole::Sales => [
                ['title' => Prospect::query()->where('assigned_sales_id', $userId)->where('priority', 'high')->whereNotIn('status', ['won', 'lost'])->count().' high-priority prospects', 'status' => 'Focus'],
                ['title' => FollowUpActivity::query()->where('user_id', $userId)->whereDate('next_follow_up_at', '<', today())->count().' overdue follow-ups', 'status' => 'Due'],
                ['title' => Quotation::query()->where('sales_id', $userId)->where('status', 'draft')->count().' quotation drafts', 'status' => 'Draft'],
            ],
            UserRole::Manager => [
                ['title' => Quotation::query()->where('status', 'submitted')->count().' quotations waiting approval', 'status' => 'Review'],
                ['title' => Prospect::query()->where('priority', 'high')->whereNotIn('status', ['won', 'lost'])->count().' high-priority prospects', 'status' => 'Pipeline'],
                ['title' => FollowUpActivity::query()->whereDate('next_follow_up_at', '<', today())->count().' overdue follow-ups across team', 'status' => 'Team'],
            ],
            UserRole::Finance => [
                ['title' => Quotation::query()->where('status', 'approved')->whereNull('pdf_path')->count().' approved quotations need PDF', 'status' => 'PDF'],
                ['title' => Quotation::query()->where('status', 'approved')->count().' quotations ready for contract', 'status' => 'Contract'],
                ['title' => Quotation::query()->where('status', 'accepted')->count().' accepted quotations for handoff', 'status' => 'Handoff'],
            ],
        };
    }

    /**
     * @return list<array{stage: string, count: int}>
     */
    private function pipelineSnapshot(UserRole $role, int $userId): array
    {
        $counts = Prospect::query()
            ->when($role === UserRole::Sales, fn (Builder $query) => $query->where('assigned_sales_id', $userId))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(Prospect::STATUSES)
            ->map(fn (string $status): array => [
                'stage' => str($status)->headline()->toString(),
                'count' => (int) ($counts[$status] ?? 0),
            ])
            ->all();
    }

    /**
     * @return Collection<int, Quotation>
     */
    private function recentQuotations(UserRole $role, int $userId)
    {
        return Quotation::query()
            ->with(['prospect', 'sales'])
            ->when($role === UserRole::Sales, fn (Builder $query) => $query->where('sales_id', $userId))
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, FollowUpActivity>
     */
    private function overdueFollowUps(UserRole $role, int $userId)
    {
        if ($role === UserRole::Finance) {
            return FollowUpActivity::query()->whereRaw('1 = 0')->get();
        }

        return FollowUpActivity::query()
            ->with(['prospect', 'user'])
            ->whereDate('next_follow_up_at', '<', today())
            ->when($role === UserRole::Sales, fn (Builder $query) => $query->where('user_id', $userId))
            ->oldest('next_follow_up_at')
            ->limit(5)
            ->get();
    }

    private function conversionRate(): string
    {
        $total = Prospect::query()->count();

        if ($total === 0) {
            return '0%';
        }

        $won = Prospect::query()->where('status', 'won')->count();

        return number_format(($won / $total) * 100, 1).'%';
    }

    private function formatCurrency(float|int|string|null $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }
}
