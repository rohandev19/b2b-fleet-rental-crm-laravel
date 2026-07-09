<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quotation\StoreQuotationRequest;
use App\Models\Prospect;
use App\Models\ProspectContact;
use App\Models\Quotation;
use App\Models\RentalPackage;
use App\Models\Vehicle;
use App\Services\Quotation\QuotationCalculator;
use App\Services\Quotation\QuotationNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(Request $request): View
    {
        $quotations = Quotation::query()
            ->with(['prospect', 'sales'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('quotation_number', 'like', "%{$search}%")
                        ->orWhereHas('prospect', fn ($query) => $query->where('company_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('quotations.index', [
            'quotations' => $quotations,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('quotations.create', [
            'prospects' => Prospect::query()
                ->whereNotIn('status', ['lost'])
                ->orderBy('company_name')
                ->get(['id', 'company_name']),
            'contacts' => ProspectContact::query()
                ->with('prospect:id,company_name')
                ->orderBy('name')
                ->get(['id', 'prospect_id', 'name', 'position']),
            'vehicles' => Vehicle::query()
                ->where('is_active', true)
                ->orderBy('brand')
                ->orderBy('model')
                ->get(),
            'rentalPackages' => RentalPackage::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(
        StoreQuotationRequest $request,
        QuotationCalculator $calculator,
        QuotationNumberGenerator $numberGenerator,
    ): RedirectResponse {
        $validated = $request->validated();

        $quotation = DB::transaction(function () use ($request, $validated, $calculator, $numberGenerator) {
            $calculation = $calculator->calculate(
                $validated['items'],
                (float) ($validated['discount_amount'] ?? 0),
                (float) ($validated['tax_percent'] ?? 11),
            );

            $quotation = Quotation::query()->create([
                'quotation_number' => $numberGenerator->generate($request->date('quotation_date')),
                'prospect_id' => $validated['prospect_id'],
                'contact_id' => $validated['contact_id'] ?? null,
                'sales_id' => $request->user()->id,
                'quotation_date' => $request->date('quotation_date'),
                'valid_until' => $request->date('valid_until'),
                'status' => 'draft',
                'subtotal' => $calculation['subtotal'],
                'discount_amount' => $calculation['discount_amount'],
                'tax_percent' => $calculation['tax_percent'],
                'tax_amount' => $calculation['tax_amount'],
                'grand_total' => $calculation['grand_total'],
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'internal_notes' => $validated['internal_notes'] ?? null,
            ]);

            $quotation->items()->createMany($calculation['items']);
            $quotation->prospect()->update(['status' => 'quotation']);

            return $quotation;
        });

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation draft created successfully.');
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load(['prospect', 'contact', 'sales', 'approvedBy', 'items.vehicle', 'items.package', 'approvals.user']);

        return view('quotations.show', [
            'quotation' => $quotation,
        ]);
    }
}
