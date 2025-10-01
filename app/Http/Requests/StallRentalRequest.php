<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StallRentalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // allow all authorized users; update if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $stallRentalId = $this->route('stallRental')?->id;
        $step = $this->input('step');

        $rules = [
            'step' => [ $isCreate ? 'required' : 'nullable', 'integer', 'in:1,2,3,4'],
            'stall_id' => [$isCreate ? 'required' : 'nullable', 'integer', 'exists:stalls,id'],
            'business_name' => [
                $isCreate ? 'required' : 'nullable',
                'string',
                'max:255',
                Rule::unique('stall_rentals')->ignore($stallRentalId)
            ],
            'fees' => ['nullable', 'array'],
            'bulb' => ['nullable', 'integer', 'min:0'],
            'total_payment' => ['nullable', 'numeric'],
        ];

        // Step 2 & 4 → allow requirements
        if (in_array($step, [2, 4])) {
            $rules += [
                'requirements' => ['nullable', 'array'],
                'requirements.*.requirement_checklist_id' => [
                    'integer',
                    'exists:requirement_checklists,id'
                ],
                'requirements.*.attachment' => [
                    $isCreate ? 'required' : 'nullable',
                    'file',
                    'mimes:jpg,jpeg,png,pdf',
                    'max:2048'
                ],
            ];
        }

        // Step 3 & 4 → allow signature
        if (in_array($step, [3, 4])) {
            $rules += [
                'attachment_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png'],
                'acknowledgeContract' => 'boolean|nullable'
            ];
        }

        return $rules;
    }

    /**
     * Prepare inputs before validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            // If bulb is not provided, default to 0
            'bulb' => $this->input('bulb', 0),
            // Always default requirements to array so backend won’t break
            'requirements' => $this->input('requirements', []),
        ]);
    }
}
