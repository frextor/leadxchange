<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
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
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'city_birth' => ['required', 'string', 'max:255'],
            'city_living' => ['required', 'string', 'max:255'],
            'birthday' => ['required', 'date', 'before:today'],
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
            'birthday.before' => 'Birthday must be a date before today.',
            'gender.in' => 'Gender must be male, female, or other.',
        ];
    }
}
