<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'siret' => ['required', 'string', 'size:14', 'unique:companies,siret', 'regex:/^[0-9]{14}$/'],
            'sector' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
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
            'siret.unique' => 'This SIRET number is already registered.',
            'siret.size' => 'SIRET must be exactly 14 digits.',
            'siret.regex' => 'SIRET must contain only numbers.',
        ];
    }
}
