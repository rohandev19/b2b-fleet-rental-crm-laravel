<?php

namespace App\Http\Controllers;

use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $endDate = $request->date('end_date')?->endOfDay() ?? now()->endOfMonth();

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return view('reports.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $this->summary($startDate, $endDate),
            'prospectFunnel' => $this->prospectFunnel(),
            'quotationStatus' => $this->quotationStatus($startDate, $endDate),
            'followUpOutcomes' => $this->followUpOutcomes($startDate, $endDate),
            'salesPerformance' => $this->salesPerformance($startDate, $endDate),
        ]);
    }

    /**
     * @return array{new_prospects: int, quotations: int, approved_value: string, follow_ups: int}
     */
    private function summary(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'new_prospects' => Prospect::query()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'quotations' => Quotation::query()->whereBetween('quotation_date', [$startDate->toDateString(), $endDate->toDateString()])->count(),
            'approved_value' => $this->formatCurrency(
                Quotation::query()
                    ->where('status', 'approved')
                    ->whereBetween('quotation_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('grand_total')
            ),
            'follow_ups' => FollowUpActivity::query()->whereBetween('activity_date', [$startDate, $endDate])->count(),
        ];
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function prospectFunnel(): array
    {
        $counts = Prospect::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(Prospect::STATUSES)
            ->map(fn (string $status): array => [
                'label' => str($status)->headline()->toString(),
                'count' => (int) ($counts[$status] ?? 0),
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int, value: string}>
     */
    private function quotationStatus(Carbon $startDate, Carbon $endDate): array
    {
        $rows = Quotation::query()
            ->whereBetween('quotation_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('status, count(*) as total, sum(grand_total) as value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return collect(Quotation::STATUSES)
            ->map(fn (string $status): array => [
                'label' => str($status)->headline()->toString(),
                'count' => (int) ($rows[$status]->total ?? 0),
                'value' => $this->formatCurrency($rows[$status]->value ?? 0),
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function followUpOutcomes(Carbon $startDate, Carbon $endDate): array
    {
        $counts = FollowUpActivity::query()
            ->whereBetween('activity_date', [$startDate, $endDate])
            ->selectRaw("coalesce(outcome, 'pending') as outcome_label, count(*) as total")
            ->groupBy('outcome_label')
            ->pluck('total', 'outcome_label');

        return collect(['positive', 'neutral', 'negative', 'no_response', 'pending'])
            ->map(fn (string $outcome): array => [
                'label' => $outcome === 'pending' ? 'Pending Outcome' : str($outcome)->headline()->toString(),
                'count' => (int) ($counts[$outcome] ?? 0),
            ])
            ->all();
    }

    /**
     * @return list<array{name: string, prospects: int, quotations: int, approved_value: string}>
     */
    private function salesPerformance(Carbon $startDate, Carbon $endDate): array
    {
        return User::query()
            ->where('role', 'sales')
            ->withCount([
                'assignedProspects as prospects_count',
                'quotations as quotations_count' => fn ($query) => $query->whereBetween('quotation_date', [$startDate->toDateString(), $endDate->toDateString()]),
            ])
            ->withSum([
                'quotations as approved_value' => fn ($query) => $query
                    ->where('status', 'approved')
                    ->whereBetween('quotation_date', [$startDate->toDateString(), $endDate->toDateString()]),
            ], 'grand_total')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'name' => $user->name,
                'prospects' => (int) $user->prospects_count,
                'quotations' => (int) $user->quotations_count,
                'approved_value' => $this->formatCurrency($user->approved_value ?? 0),
            ])
            ->all();
    }

    private function formatCurrency(float|int|string|null $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }
}
