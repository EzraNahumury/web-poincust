<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TransactionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'gross_amount' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'transaction_discount' => ['nullable', 'integer', 'min:0', 'lte:gross_amount'],
            'points_redeemed' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'transaction_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'external_receipt_no' => ['nullable', 'string', 'max:100'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_discount.lte' => 'Diskon transaksi tidak boleh melebihi total belanja.',
        ];
    }
}
