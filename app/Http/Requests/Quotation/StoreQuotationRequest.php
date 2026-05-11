<?php

namespace App\Http\Requests\Quotation;

use App\Enums\UserRole;
use App\Models\ProspectContact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin, UserRole::Sales) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prospect_id' => ['required', 'exists:prospects,id'],
            'contact_id' => ['nullable', 'exists:prospect_contacts,id'],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:quotation_date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'terms_and_conditions' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('is_active', true)],
            'items.*.package_id' => ['nullable', Rule::exists('rental_packages', 'id')->where('is_active', true)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.duration_months' => ['required', 'integer', 'min:1'],
            'items.*.monthly_price' => ['required', 'numeric', 'min:1'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled(['quotation_date', 'valid_until'])) {
                try {
                    $quotationDate = Carbon::parse($this->input('quotation_date'));
                    $validUntil = Carbon::parse($this->input('valid_until'));
                } catch (\Throwable) {
                    return;
                }

                if ($validUntil->lt($quotationDate->copy()->addDays(7))) {
                    $validator->errors()->add('valid_until', 'Valid until must be at least 7 days after the quotation date.');
                }
            }

            if ($this->filled('contact_id')) {
                $belongsToProspect = ProspectContact::query()
                    ->whereKey($this->integer('contact_id'))
                    ->where('prospect_id', $this->integer('prospect_id'))
                    ->exists();

                if (! $belongsToProspect) {
                    $validator->errors()->add('contact_id', 'The selected contact does not belong to this prospect.');
                }
            }
        });
    }
}
