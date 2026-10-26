<?php

namespace App\Http\Controllers\Member;

use App\Domain\Membership\CustomerRegistrar;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('member.dashboard');
        }

        return view('member.auth.register');
    }

    public function store(RegisterRequest $request, CustomerRegistrar $registrar): RedirectResponse
    {
        $data = $request->validated();

        $customer = $registrar->register([
            'name' => $data['name'],
            'birth_date' => $data['birth_date'],
            'whatsapp_number' => $data['whatsapp_number'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'pin' => $data['pin'],
        ]);

        // Log the new member in and harden the session against fixation.
        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('member.dashboard')
            ->with('success', 'Selamat datang di member Avasport, ' . $customer->name . '!');
    }
}
