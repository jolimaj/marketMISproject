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
            'stall_id' => [$isCreate ? 'required' : 'nullable', 'integer'],
            'business_name' => [
                $isCreate ? 'required' : 'nullable',
                'string',
                'max:255',
                Rule::unique('volantes', 'business_name')->ignore($volanteRentalId, 'id')
            ],
            'quantity' => [$isCreate ? 'required' : 'nullable', 'numeric', 'min:1'],
            'location' => ['nullable', 'string', 'max:500'],
            'duration' => [$isCreate ? 'required' : 'nullable', 'integer', 'in:1,2,3'],
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
