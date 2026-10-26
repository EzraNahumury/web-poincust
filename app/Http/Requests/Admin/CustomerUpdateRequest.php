<?php

namespace App\Http\Requests\Admin;

use App\Models\Customer;
use App\Support\WhatsApp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
        $customerId = $this->route('customer')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'whatsapp_number' => [
                'required', 'string',
                fn ($a, $v, $fail) => WhatsApp::isValid($v) ?: $fail('Nomor WhatsApp tidak valid.'),
                Rule::unique('customers', 'whatsapp_number')->ignore($customerId),
            ],
            'username' => [
                'required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9._-]+$/',
                Rule::unique('customers', 'username')->ignore($customerId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
