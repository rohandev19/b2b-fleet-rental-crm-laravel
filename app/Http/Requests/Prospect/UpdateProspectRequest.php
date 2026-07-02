<?php

namespace App\Http\Requests\Prospect;

use App\Enums\UserRole;
use App\Models\Prospect;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProspectRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:150'],
            'industry' => ['nullable', 'string', 'max:100'],
            'company_size' => ['required', Rule::in(Prospect::COMPANY_SIZES)],
            'address' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'source' => ['required', Rule::in(Prospect::SOURCES)],
            'status' => ['required', Rule::in(Prospect::STATUSES)],
            'priority' => ['required', Rule::in(Prospect::PRIORITIES)],
            'estimated_vehicle_need' => ['nullable', 'integer', 'min:0'],
            'assigned_sales_id' => ['nullable', 'exists:users,id'],
            'next_follow_up_at' => ['required_if:priority,high', 'nullable', 'date'],
            'lost_reason' => ['required_if:status,lost', 'nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->duplicateCompanyExists()) {
                $validator->errors()->add('company_name', 'A prospect with this company name already exists.');
            }

            if ($this->input('status') === 'won') {
                $validator->errors()->add('status', 'Won status is set automatically when a sent quotation is accepted.');
            }
        });
    }

    private function duplicateCompanyExists(): bool
    {
        $prospect = $this->route('prospect');

        return Prospect::query()
            ->whereRaw('lower(company_name) = ?', [mb_strtolower(trim((string) $this->input('company_name')))])
            ->whereNull('deleted_at')
            ->when($prospect, fn ($query) => $query->whereKeyNot($prospect->getKey()))
            ->exists();
    }
}
