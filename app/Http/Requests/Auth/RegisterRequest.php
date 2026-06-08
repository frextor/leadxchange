<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
        return [
            'first_name'         => ['required', 'string', 'max:255'],
            'last_name'          => ['required', 'string', 'max:255'],
            'email'              => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'           => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'phone'              => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'phone_country_code' => ['nullable', 'string', 'max:10'],
            'phone_code'         => ['nullable', 'string', 'max:10'],
            'city_id'            => ['nullable', 'integer', 'exists:cities,id'],
            'nationality_id'     => ['nullable', 'integer', 'exists:countries,id'],
            'gender'             => ['required', 'string', 'in:male,female,other'],
            'birthday'           => ['required', 'date', 'before:today'],
            'invitation_token'    => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique'    => 'This email is already registered.',
            'first_name.required' => 'First name is required.',
            'last_name.required'  => 'Last name is required.',
            'phone.unique'    => 'This phone number is already registered.',
        ];
    }
}
