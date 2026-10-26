<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\AuditLogger;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('member.profile', [
            'customer' => Auth::guard('customer')->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $request->merge([
            'whatsapp_number' => WhatsApp::normalize((string) $request->input('whatsapp_number'))
                ?? (string) $request->input('whatsapp_number'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapp_number' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    if (! WhatsApp::isValid($value)) {
                        $fail('Nomor WhatsApp tidak valid.');
                    }
                },
                Rule::unique('customers', 'whatsapp_number')->ignore($customer->id),
            ],
        ]);

        $customer->fill($validated)->save();

        AuditLogger::log('customer.profile_update', [
            'actor_type' => 'customer',
            'actor_id' => $customer->id,
            'entity_type' => 'Customer',
            'entity_id' => $customer->id,
        ]);

        return redirect()->route('member.profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
