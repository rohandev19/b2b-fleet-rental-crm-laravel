<?php

namespace App\Http\Requests\Vehicle;

use App\Enums\UserRole;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
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
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'vehicle_type' => [
                'required',
                Rule::in(Vehicle::TYPES),
                Rule::unique('vehicles', 'vehicle_type')->where(fn ($query) => $query
                    ->where('brand', $this->string('brand')->toString())
                    ->where('model', $this->string('model')->toString())),
            ],
            'transmission' => ['required', Rule::in(Vehicle::TRANSMISSIONS)],
            'fuel_type' => ['required', Rule::in(Vehicle::FUEL_TYPES)],
            'seat_capacity' => ['required', 'integer', 'min:1', 'max:80'],
            'base_monthly_price' => ['required', 'numeric', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
