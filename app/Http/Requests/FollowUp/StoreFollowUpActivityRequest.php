<?php

namespace App\Http\Requests\FollowUp;

use App\Enums\UserRole;
use App\Models\FollowUpActivity;
use App\Models\ProspectContact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFollowUpActivityRequest extends FormRequest
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
            'contact_id' => ['nullable', 'exists:prospect_contacts,id'],
            'activity_type' => ['required', Rule::in(FollowUpActivity::TYPES)],
            'activity_date' => ['required', 'date'],
            'summary' => ['required', 'string', 'max:180'],
            'detail' => ['nullable', 'string', 'max:3000'],
            'next_follow_up_at' => ['nullable', 'date'],
            'outcome' => ['nullable', Rule::in(FollowUpActivity::OUTCOMES)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $prospect = $this->route('prospect');

            if ($prospect?->status === 'lost') {
                $validator->errors()->add('prospect_id', 'Follow-up cannot be added to a lost prospect.');
            }

            if ($this->filled('contact_id')) {
                $belongsToProspect = ProspectContact::query()
                    ->whereKey($this->integer('contact_id'))
                    ->where('prospect_id', $prospect?->id)
                    ->exists();

                if (! $belongsToProspect) {
                    $validator->errors()->add('contact_id', 'The selected contact does not belong to this prospect.');
                }
            }
        });
    }
}
