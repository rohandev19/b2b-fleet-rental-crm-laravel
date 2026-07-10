<?php

namespace App\Http\Controllers;

use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$startDate, $endDate] = $this->reportPeriod($request);

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

    public function exportProspects(Request $request): StreamedResponse
    {
        [$startDate, $endDate] = $this->reportPeriod($request);
        $filename = 'prospects-'.$startDate->toDateString().'-to-'.$endDate->toDateString().'.csv';

        return response()->streamDownload(function () use ($startDate, $endDate): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Company Name',
                'Industry',
                'City',
                'Status',
                'Priority',
                'Estimated Vehicle Need',
                'Assigned Sales',
                'Contacts',
                'Next Follow Up',
                'Created At',
            ]);

            Prospect::query()
                ->with('assignedSales:id,name')
                ->withCount('contacts')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('company_name')
                ->chunk(100, function ($prospects) use ($handle): void {
                    foreach ($prospects as $prospect) {
                        fputcsv($handle, [
                            $prospect->company_name,
                            $prospect->industry,
                            $prospect->city,
                            str($prospect->status)->headline()->toString(),
                            str($prospect->priority)->headline()->toString(),
                            $prospect->estimated_vehicle_need,
                            $prospect->assignedSales?->name,
                            $prospect->contacts_count,
                            $prospect->next_follow_up_at?->toDateTimeString(),
                            $prospect->created_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportQuotations(Request $request): StreamedResponse
    {
        [$startDate, $endDate] = $this->reportPeriod($request);
        $filename = 'quotations-'.$startDate->toDateString().'-to-'.$endDate->toDateString().'.csv';

        return response()->streamDownload(function () use ($startDate, $endDate): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Quotation Number',
                'Prospect',
                'Sales',
                'Quotation Date',
                'Valid Until',
                'Status',
                'Subtotal',
                'Discount',
                'Tax',
                'Grand Total',
            ]);

            Quotation::query()
                ->with(['prospect:id,company_name', 'sales:id,name'])
                ->whereBetween('quotation_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->orderBy('quotation_date')
                ->orderBy('quotation_number')
                ->chunk(100, function ($quotations) use ($handle): void {
                    foreach ($quotations as $quotation) {
                        fputcsv($handle, [
                            $quotation->quotation_number,
                            $quotation->prospect?->company_name,
                            $quotation->sales?->name,
                            $quotation->quotation_date?->toDateString(),
                            $quotation->valid_until?->toDateString(),
                            str($quotation->status)->headline()->toString(),
                            $quotation->subtotal,
                            $quotation->discount_amount,
                            $quotation->tax_amount,
                            $quotation->grand_total,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function reportPeriod(Request $request): array
    {
        $startDate = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $endDate = $request->date('end_date')?->endOfDay() ?? now()->endOfMonth();

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [$startDate, $endDate];
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
