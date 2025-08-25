<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
        $isCreate = $this->isMethod('post'); // true for create, false for update
        return [
            'reference_number' => [$isCreate ? 'required' : 'nullable', 'integer'],
            'amount' => [$isCreate ? 'required' : 'nullable', 'integer'],
            'receipt' => [$isCreate ? 'required' : 'nullable','numeric','decimal:0,2'],
            'date' => 'string|max:255',
        ];
    }
}
