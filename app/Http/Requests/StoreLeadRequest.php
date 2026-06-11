<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id'      => [
                'required', 'integer', 'exists:users,id',
                function ($_attr, $value, $fail) {
                    if ((int) $value === $this->user()->id) {
                        $fail('Vous ne pouvez pas vous envoyer un lead à vous-même.');
                    }
                },
            ],
            'company_name'     => ['required', 'string', 'max:150'],
            'contact_name'     => ['required', 'string', 'max:100'],
            'contact_email'    => ['required', 'email', 'max:150'],
            'contact_phone'    => ['required', 'string', 'max:30'],
            'contact_position' => ['nullable', 'string', 'max:100'],
            'deadline'         => ['required', 'date', 'after:today'],
            'qualification'    => ['required', 'in:chaud,tiede,froid'],
            'sector_id'        => ['required', 'integer', 'exists:sectors,id'],
            'description'      => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.required'   => 'Veuillez sélectionner un destinataire.',
            'receiver_id.exists'     => 'Destinataire introuvable.',
            'company_name.required'  => 'Le nom de l\'entreprise est obligatoire.',
            'contact_name.required'  => 'Le nom du contact est obligatoire.',
            'contact_email.required' => 'L\'email du contact est obligatoire.',
            'contact_email.email'    => 'Veuillez saisir une adresse email valide.',
            'contact_phone.required' => 'Le téléphone du contact est obligatoire.',
            'deadline.required'      => 'Veuillez définir une deadline.',
            'deadline.after'         => 'La deadline doit être une date future.',
            'qualification.required' => 'Veuillez sélectionner un niveau de qualification.',
            'qualification.in'       => 'La qualification doit être Chaud, Tiède ou Froid.',
            'sector_id.required'     => 'Veuillez sélectionner un secteur.',
            'sector_id.exists'       => 'Secteur invalide.',
        ];
    }
}
