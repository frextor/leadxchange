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
                        $fail('You cannot send a lead to yourself.');
                    }
                },
            ],
            'company_name'     => ['required', 'string', 'max:150'],
            'contact_name'     => ['required', 'string', 'max:100'],
            'contact_email'    => ['nullable', 'email', 'max:150'],
            'contact_phone'    => ['nullable', 'string', 'max:30'],
            'contact_position' => ['nullable', 'string', 'max:100'],
            'deadline'         => ['required', 'date', 'after:today'],
            'qualification'    => ['required', 'in:chaud,tiede,froid'],
            'description'      => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.required'   => 'Please select a recipient.',
            'receiver_id.exists'     => 'Recipient not found.',
            'company_name.required'  => 'The company name is required.',
            'contact_name.required'  => 'The contact name is required.',
            'contact_email.email'    => 'Please enter a valid email address.',
            'deadline.required'      => 'Please set a deadline.',
            'deadline.after'         => 'The deadline must be a future date.',
            'qualification.required' => 'Please select a qualification level.',
            'qualification.in'       => 'Qualification must be Chaud, Tiède, or Froid.',
        ];
    }
}
