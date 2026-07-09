<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Services\Quotation\QuotationPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuotationPdfController extends Controller
{
    public function generate(Quotation $quotation, QuotationPdfService $service): RedirectResponse
    {
        $service->generate($quotation);

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation PDF generated successfully.');
    }

    public function download(Quotation $quotation, QuotationPdfService $service): RedirectResponse|StreamedResponse
    {
        if ($quotation->status !== 'approved') {
            throw ValidationException::withMessages([
                'status' => 'Only approved quotations can be downloaded as PDF.',
            ]);
        }

        if (! $quotation->pdf_path || ! Storage::disk('local')->exists($quotation->pdf_path)) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->withErrors(['pdf_path' => 'Generate the quotation PDF before downloading it.']);
        }

        return Storage::disk('local')->download(
            $quotation->pdf_path,
            $service->downloadFilename($quotation),
        );
    }
}
