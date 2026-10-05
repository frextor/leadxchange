<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSmtpSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'host'         => ['required', 'string', 'max:255'],
            'port'         => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption'   => ['required', 'in:tls,ssl,none'],
            'username'     => ['required', 'string', 'max:255', 'email'],
            'password'     => ['nullable', 'string', 'max:500'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name'    => ['required', 'string', 'max:100'],
            'is_active'    => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $port = (int) $this->input('port');
            $enc  = $this->input('encryption');

            if ($port === 465 && $enc !== 'ssl') {
                $v->errors()->add('encryption', 'Le port 465 exige le chiffrement SSL.');
            }
            if ($port === 587 && $enc !== 'tls') {
                $v->errors()->add('encryption', 'Le port 587 exige le chiffrement TLS.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'host.required'         => 'Le serveur SMTP est obligatoire.',
            'port.required'         => 'Le port SMTP est obligatoire.',
            'port.integer'          => 'Le port doit être un nombre entier.',
            'encryption.in'         => 'Le chiffrement doit être tls, ssl ou none.',
            'username.required'     => 'Le nom d\'utilisateur SMTP est obligatoire.',
            'username.email'        => 'Le nom d\'utilisateur doit être une adresse email valide.',
            'from_address.required' => 'L\'adresse expéditeur est obligatoire.',
            'from_address.email'    => 'L\'adresse expéditeur doit être une adresse email valide.',
            'from_name.required'    => 'Le nom expéditeur est obligatoire.',
        ];
    }
}
