<?php

namespace App\Http\Requests\RentalPackage;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRentalPackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('rental_packages', 'name')->ignore($this->route('rentalPackage')),
            ],
            'description' => ['nullable', 'string', 'max:3000'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'includes_driver' => ['sometimes', 'boolean'],
            'includes_maintenance' => ['sometimes', 'boolean'],
            'includes_insurance' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
