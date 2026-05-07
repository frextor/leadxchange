<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            // ── Users table ──────────────────────────────────────────────
            'first_name'       => ['sometimes', 'string', 'max:100'],
            'last_name'        => ['sometimes', 'string', 'max:100'],
            'phone'            => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($userId)],
            'gender'           => ['required', Rule::in(['male', 'female', 'other'])],
            'birthday'         => ['required', 'date', 'before:today'],
            'city_birth'       => ['required', 'string', 'max:255'],
            'city_living'      => ['required', 'string', 'max:255'],
            'nationality_id'   => ['nullable', 'integer', 'exists:nationalities,id'],

            // ── Profiles table ───────────────────────────────────────────
            'bio'              => ['nullable', 'string', 'max:1000'],
            'motto'            => ['nullable', 'string', 'max:500'],
            'job_title'        => ['nullable', 'string', 'max:150'],
            'experience_level' => ['nullable', Rule::in(['junior', 'mid', 'senior', 'expert'])],
            'looking_for'      => ['nullable', 'string', 'max:1000'],
            'services_offered' => ['nullable', 'string', 'max:1000'],
            'open_to_network'  => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'gender.required'        => 'Gender is required.',
            'gender.in'              => 'Gender must be male, female, or other.',
            'birthday.required'      => 'Birthday is required.',
            'birthday.before'        => 'Birthday must be a date before today.',
            'city_birth.required'    => 'City of birth is required.',
            'city_living.required'   => 'City of living is required.',
            'nationality_id.exists'  => 'Selected nationality is invalid.',
            'experience_level.in'    => 'Experience level must be junior, mid, senior, or expert.',
            'phone.unique'           => 'This phone number is already taken.',
        ];
    }
}
