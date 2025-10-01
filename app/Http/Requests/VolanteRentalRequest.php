<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class VolanteRentalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $volanteRentalId = $this->route('volanteRental') ? $this->route('volanteRental')->id : null;
        Log::info('VolanteRentalRequest rules called', [
            'volanteRentalId' => $volanteRentalId,
            'method' => $this->method(),
            'all' => $this->all(),
        ]);

        $isCreate = $this->isMethod('post'); // true for create, false for update

        return [
            'step' => ['integer'],
            'stall_id' => [$isCreate ? 'required' : 'nullable', 'integer', 'exists:stalls,id'],
            
            'business_name' => [
                $isCreate ? 'required' : 'nullable',
                'string',
                'max:255',
                Rule::unique('stall_rentals')->ignore($volanteRentalId)
            ],
            'attachment_signature' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
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
            'fees' => ['nullable','array'],
            'bulb' => ['nullable','integer','min:0'],
        ];
    }

}
