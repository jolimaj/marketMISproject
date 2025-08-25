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
        $stallRentalId = $this->route('stallRental')?->id; // only set if editing

        return [
            'step' => ['integer'],
            'stall_id' => [$isCreate ? 'required' : 'nullable', 'integer', 'exists:stalls,id'],
            
            'business_name' => [
                $isCreate ? 'required' : 'nullable',
                'string',
                'max:255',
                Rule::unique('stall_rentals')->ignore($stallRentalId)
            ],
            'started_date' => [$isCreate ? 'required' : 'nullable', 'date'],
            'end_date' => [$isCreate ? 'required' : 'nullable', 'date', 'after_or_equal:started_date'],
            'requirements' => ['nullable','array'],
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
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'amount' => ['numeric','decimal:0,2'],
        ];
    }
}
