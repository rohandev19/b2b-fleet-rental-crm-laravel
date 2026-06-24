<?php

namespace App\Services\Quotation;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotationApprovalService
{
    public function submit(Quotation $quotation, User $user): Quotation
    {
        if (! in_array($quotation->status, ['draft', 'rejected', 'revised'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only draft, rejected, or revised quotations can be submitted.',
            ]);
        }

        return $this->transition($quotation, $user, 'submit', 'submitted');
    }

    public function approve(Quotation $quotation, User $user): Quotation
    {
        if ($quotation->status !== 'submitted') {
            throw ValidationException::withMessages([
                'status' => 'Only submitted quotations can be approved.',
            ]);
        }

        if ($quotation->sales_id === $user->id) {
            throw ValidationException::withMessages([
                'approved_by' => 'Sales cannot approve their own quotation.',
            ]);
        }

        return DB::transaction(function () use ($quotation, $user) {
            $this->transition($quotation, $user, 'approve', 'approved');

            $quotation->update([
                'approved_by' => $user->id,
            ]);

            return $quotation->refresh();
        });
    }

    public function reject(Quotation $quotation, User $user, string $reason): Quotation
    {
        if ($quotation->status !== 'submitted') {
            throw ValidationException::withMessages([
                'status' => 'Only submitted quotations can be rejected.',
            ]);
        }

        if ($quotation->sales_id === $user->id) {
            throw ValidationException::withMessages([
                'approved_by' => 'Sales cannot reject their own quotation.',
            ]);
        }

        return $this->transition($quotation, $user, 'reject', 'rejected', $reason);
    }

    public function markSent(Quotation $quotation, User $user): Quotation
    {
        if ($quotation->status !== 'approved') {
            throw ValidationException::withMessages([
                'status' => 'Only approved quotations can be marked as sent.',
            ]);
        }

        if (! $quotation->pdf_path) {
            throw ValidationException::withMessages([
                'pdf_path' => 'Generate the approved quotation PDF before marking it as sent.',
            ]);
        }

        return $this->transition($quotation, $user, 'mark_sent', 'sent');
    }

    public function accept(Quotation $quotation, User $user): Quotation
    {
        if ($quotation->status !== 'sent') {
            throw ValidationException::withMessages([
                'status' => 'Only sent quotations can be accepted.',
            ]);
        }

        return $this->transition($quotation, $user, 'accept', 'accepted');
    }

    public function decline(Quotation $quotation, User $user): Quotation
    {
        if ($quotation->status !== 'sent') {
            throw ValidationException::withMessages([
                'status' => 'Only sent quotations can be declined.',
            ]);
        }

        return $this->transition($quotation, $user, 'decline', 'declined');
    }

    private function transition(Quotation $quotation, User $user, string $action, string $toStatus, ?string $reason = null): Quotation
    {
        return DB::transaction(function () use ($quotation, $user, $action, $toStatus, $reason) {
            $fromStatus = $quotation->status;

            $quotation->update([
                'status' => $toStatus,
            ]);

            $quotation->approvals()->create([
                'user_id' => $user->id,
                'action' => $action,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason' => $reason,
            ]);

            return $quotation->refresh();
        });
    }
}
