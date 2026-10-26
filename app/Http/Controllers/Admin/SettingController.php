<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'rupiahPerPoint' => Setting::get(config('points.keys.rupiah_per_point'), config('points.rupiah_per_point')),
            'redeemValue' => Setting::get(config('points.keys.redeem_value'), config('points.redeem_value')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rupiah_per_point' => ['required', 'integer', 'min:1', 'max:100000000'],
            'redeem_value' => ['required', 'integer', 'min:1', 'max:100000000'],
        ]);

        $userId = Auth::guard('web')->id();

        Setting::put(config('points.keys.rupiah_per_point'), $validated['rupiah_per_point'], 'integer', $userId);
        Setting::put(config('points.keys.redeem_value'), $validated['redeem_value'], 'integer', $userId);

        AuditLogger::log('settings.update', [
            'after' => $validated,
        ]);

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan program poin diperbarui.');
    }
}
