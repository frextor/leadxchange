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
            // ── Users table ───────────────────────────────────────────────
            'first_name'         => ['sometimes', 'string', 'max:100'],
            'last_name'          => ['sometimes', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($userId)],
            'phone_country_code' => ['nullable', 'string', 'max:10'],  // web
            'phone_code'         => ['nullable', 'string', 'max:10'],  // mobile alias
            'gender'             => ['sometimes', Rule::in(['male', 'female', 'other'])],
            'birthday'           => ['sometimes', 'date', 'before:today'],
            'city_id'     => ['nullable', 'integer', 'exists:cities,id'],
            'nationality_id'     => ['nullable', 'integer', 'exists:nationalities,id'],
            'company_id'         => ['nullable', 'integer', 'exists:companies,id'],
            'newsletter'         => ['nullable', 'boolean'],
            'notifications'      => ['nullable', 'boolean'],

            // ── Profiles table ────────────────────────────────────────────
            'bio'                => ['nullable', 'string', 'max:1000'],
            'motto'              => ['nullable', 'string', 'max:500'],
            'job_title'          => ['nullable', 'string', 'max:150'],
            'experience_level'   => ['nullable', Rule::in(['junior', 'mid', 'senior', 'expert'])],
            'looking_for'        => ['nullable', 'array'],         // web
            'looking_for.*'      => ['integer', 'exists:sectors,id'],
            'leads_wanted'       => ['nullable', 'array'],         // mobile alias
            'leads_wanted.*'     => ['integer', 'exists:sectors,id'],
            'services_offered'   => ['nullable', 'array'],         // web
            'services_offered.*' => ['integer', 'exists:sectors,id'],
            'leads_offered'      => ['nullable', 'array'],         // mobile alias
            'leads_offered.*'    => ['integer', 'exists:sectors,id'],
            'sector_id'          => ['nullable', 'array'],
            'sector_id.*'        => ['integer', 'exists:sectors,id'],
            'open_to_network'    => ['nullable', 'boolean'],
            'website'            => ['nullable', 'url', 'max:255'],
            'linkedin'           => ['nullable', 'url', 'max:255'],
            'profile_picture'    => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:3072'],
        ];
    }

    public function messages(): array
    {
        return [
            'gender.in'         => 'Gender must be male, female, or other.',
            'birthday.before'   => 'Birthday must be a date before today.',
            'nationality_id.exists' => 'Selected nationality is invalid.',
            'experience_level.in'   => 'Experience level must be junior, mid, senior, or expert.',
            'phone.unique'          => 'This phone number is already taken.',
            'city_id.exists' => 'Selected city is invalid.',
            'company_id.exists'     => 'Selected company is invalid.',
        ];
    }
}
