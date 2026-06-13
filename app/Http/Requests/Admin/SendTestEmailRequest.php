<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SendTestEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'test_email' => ['required', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'test_email.required' => 'L\'adresse email de test est obligatoire.',
            'test_email.email'    => 'L\'adresse email de test doit être valide.',
        ];
    }
}
