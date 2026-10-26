<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Birthday\BirthdayService;
use App\Http\Controllers\Controller;
use App\Models\BirthdayFollowup;
use App\Models\Customer;
use App\Support\AuditLogger;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BirthdayController extends Controller
{
    public function index(Request $request, BirthdayService $birthdays): View
    {
        $days = (int) $request->input('days', 7);
        $days = max(1, min($days, 31));
        $search = trim((string) $request->input('q', ''));

        $today = $this->decorate($birthdays->today(), $search);
        $upcoming = $this->decorate($birthdays->upcoming($days), $search);

        return view('admin.birthdays.index', compact('today', 'upcoming', 'days', 'search'));
    }

    public function markContacted(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $year = Carbon::now(BirthdayService::TZ)->year;

        $followup = BirthdayFollowup::updateOrCreate(
            ['customer_id' => $customer->id, 'birthday_year' => $year],
            [
                'status' => BirthdayFollowup::STATUS_CONTACTED,
                'followed_up_by' => $request->user()->id,
                'followed_up_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ],
        );

        AuditLogger::log('birthday.followup', [
            'entity_type' => 'Customer',
            'entity_id' => $customer->id,
            'after' => ['birthday_year' => $year, 'status' => $followup->status],
        ]);

        return back()->with('success', "Ditandai sudah dihubungi: {$customer->name}.");
    }

    /**
     * Attach a wa.me link + follow-up flag to each row and apply the name
     * search filter. Opening the link is NOT proof of delivery (README §13.3).
     */
    private function decorate($rows, string $search)
    {
        return $rows
            ->when($search !== '', fn ($c) => $c->filter(
                fn ($row) => str_contains(mb_strtolower($row['customer']->name), mb_strtolower($search))
            ))
            ->map(function ($row) {
                /** @var Customer $customer */
                $customer = $row['customer'];
                $message = "Selamat ulang tahun, Kak {$customer->name}! \u{1F389} "
                    . 'Semoga selalu sehat, bahagia, dan sukses. '
                    . 'Terima kasih sudah menjadi bagian dari member Avasport!';

                $row['wa_link'] = WhatsApp::isValid($customer->whatsapp_number)
                    ? WhatsApp::link($customer->whatsapp_number, $message)
                    : null;

                return $row;
            })
            ->values();
    }
}
