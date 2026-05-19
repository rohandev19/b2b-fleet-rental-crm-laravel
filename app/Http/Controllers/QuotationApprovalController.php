<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quotation\RejectQuotationRequest;
use App\Models\Quotation;
use App\Services\AuditLogger;
use App\Services\Quotation\QuotationApprovalService;
use Illuminate\Http\RedirectResponse;

class QuotationApprovalController extends Controller
{
    public function submit(Quotation $quotation, QuotationApprovalService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $before = $quotation->only(['status', 'approved_by']);
        $service->submit($quotation, auth()->user());
        $quotation->refresh();

        $auditLogger->log(
            'quotation.submitted',
            "Submitted quotation {$quotation->quotation_number} for approval.",
            $quotation,
            $before,
            $quotation->only(['status', 'approved_by']),
        );

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation submitted for approval.');
    }

    public function approve(Quotation $quotation, QuotationApprovalService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $before = $quotation->only(['status', 'approved_by']);
        $service->approve($quotation, auth()->user());
        $quotation->refresh();

        $auditLogger->log(
            'quotation.approved',
            "Approved quotation {$quotation->quotation_number}.",
            $quotation,
            $before,
            $quotation->only(['status', 'approved_by']),
        );

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation approved successfully.');
    }

    public function reject(RejectQuotationRequest $request, Quotation $quotation, QuotationApprovalService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $before = $quotation->only(['status', 'approved_by']);
        $service->reject($quotation, $request->user(), $request->string('rejection_reason')->toString());
        $quotation->refresh();

        $auditLogger->log(
            'quotation.rejected',
            "Rejected quotation {$quotation->quotation_number}.",
            $quotation,
            $before,
            $quotation->only(['status', 'approved_by']),
            $request,
        );

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation rejected successfully.');
    }
}
