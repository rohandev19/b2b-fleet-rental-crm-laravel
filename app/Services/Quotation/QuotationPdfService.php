<?php

namespace App\Services\Quotation;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class QuotationPdfService
{
    public function generate(Quotation $quotation): Quotation
    {
        if ($quotation->status !== 'approved') {
            throw ValidationException::withMessages([
                'status' => 'Only approved quotations can be generated as PDF.',
            ]);
        }

        $quotation->loadMissing([
            'prospect',
            'contact',
            'sales',
            'approvedBy',
            'items.vehicle',
            'items.package',
        ]);

        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
        ])->setPaper('a4');

        $path = 'quotations/'.$this->downloadFilename($quotation);

        Storage::disk('local')->put($path, $pdf->output());

        $quotation->update([
            'pdf_path' => $path,
            'pdf_generated_at' => now(),
        ]);

        return $quotation->refresh();
    }

    public function downloadFilename(Quotation $quotation): string
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $quotation->quotation_number) ?: 'quotation';

        return trim($filename, '-').'.pdf';
    }
}
