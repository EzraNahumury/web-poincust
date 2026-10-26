<?php

namespace App\Http\Requests\Member;

use App\Models\Customer;
use App\Support\WhatsApp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normalise identifiers BEFORE validation so uniqueness is checked on
        // the stored form and case/format variants cannot create duplicates.
        $this->merge([
            'username' => Customer::normalizeUsername((string) $this->input('username')),
            'whatsapp_number' => WhatsApp::normalize((string) $this->input('whatsapp_number'))
                ?? (string) $this->input('whatsapp_number'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'whatsapp_number' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    if (! WhatsApp::isValid($value)) {
                        $fail('Nomor WhatsApp tidak valid. Gunakan format seperti 08xxxxxxxxxx.');
                    }
                },
                'unique:customers,whatsapp_number',
            ],
            'username' => [
                'required', 'string', 'min:3', 'max:30',
                'regex:/^[a-z0-9._-]+$/',
                'unique:customers,username',
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'pin' => ['required', 'digits:6', 'confirmed'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, titik, garis bawah, atau strip.',
            'pin.digits' => 'PIN harus terdiri dari 6 digit angka.',
            'pin.confirmed' => 'Konfirmasi PIN tidak cocok.',
            'terms.accepted' => 'Anda harus menyetujui syarat membership dan kebijakan privasi.',
        ];
    }
}
