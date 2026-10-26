<?php

namespace App\Http\Requests\Admin;

use App\Models\Customer;
use App\Support\WhatsApp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CustomerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route already guarded by permission middleware
    }

    protected function prepareForValidation(): void
    {
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
                fn ($a, $v, $fail) => WhatsApp::isValid($v) ?: $fail('Nomor WhatsApp tidak valid.'),
                'unique:customers,whatsapp_number',
            ],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9._-]+$/', 'unique:customers,username'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'pin' => ['required', 'digits:6', 'confirmed'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
