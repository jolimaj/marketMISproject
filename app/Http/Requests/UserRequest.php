<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Laravel\Jetstream\Jetstream;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

use App\Rules\MobileNumber;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $users = $this->route('admin.users.update');
        return [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'max:255',
            'last_name' => 'required|string|max:255',
            'role_id' => 'required|integer',
            'gender_id' => 'required|integer',
            'sub_admin_type_id' => 'integer',
            'department_id' => 'nullable|integer',
            'department_position_id' => 'nullable|integer',
            'address' => 'required|string|max:255',
            'mobile' => ['required', 'string', 'max:12', new MobileNumber],
            'birthday' => 'string|max:255',
            'email' => ['required','string','max:255',$users
                ? Rule::unique('users')->ignore($users) // ignore if editing
                : Rule::unique('users')],
            'password' => [
                    'nullable',            // <-- not required
                    'string',
                    'min:8',              // Minimum 8 characters
                    'confirmed',          // Requires password_confirmation field
                    'regex:/[a-z]/',      // At least one lowercase letter
                    'regex:/[A-Z]/',      // At least one uppercase letter
                    'regex:/[0-9]/',      // At least one number
                    'regex:/[@$!%*?&#]/', // At least one special character
                ],
        ];
    }

}
