<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quotation\RejectQuotationRequest;
use App\Models\Quotation;
use App\Services\Quotation\QuotationApprovalService;
use Illuminate\Http\RedirectResponse;

class QuotationApprovalController extends Controller
{
    public function submit(Quotation $quotation, QuotationApprovalService $service): RedirectResponse
    {
        $service->submit($quotation, auth()->user());

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation submitted for approval.');
    }

    public function approve(Quotation $quotation, QuotationApprovalService $service): RedirectResponse
    {
        $service->approve($quotation, auth()->user());

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation approved successfully.');
    }

    public function reject(RejectQuotationRequest $request, Quotation $quotation, QuotationApprovalService $service): RedirectResponse
    {
        $service->reject($quotation, $request->user(), $request->string('rejection_reason')->toString());

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation rejected successfully.');
    }
}
