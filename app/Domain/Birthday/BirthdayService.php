<?php

namespace App\Domain\Birthday;

use App\Models\BirthdayFollowup;
use App\Models\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Birthday reminders for the admin panel (README §13).
 *
 * Matching is by birth MONTH + DAY (year is ignored). All date maths use the
 * Asia/Jakarta timezone. Customers born on 29 February are shown on 28 February
 * in non-leap years (documented rule).
 */
class BirthdayService
{
    public const TZ = 'Asia/Jakarta';

    /** Customers whose birthday is today. */
    public function today(?Carbon $reference = null): Collection
    {
        return $this->range(0, 0, $reference);
    }

    /** Customers with a birthday within the next $days days (excluding today). */
    public function upcoming(int $days = 7, ?Carbon $reference = null): Collection
    {
        return $this->range(1, $days, $reference);
    }

    /**
     * Customers with a birthday between $offsetStart and $offsetEnd days from
     * the reference date (inclusive). Returns a collection of arrays:
     * ['customer', 'birthday_on' (Carbon), 'days_until' (int), 'is_today' (bool),
     *  'followup' (BirthdayFollowup|null)].
     */
    public function range(int $offsetStart, int $offsetEnd, ?Carbon $reference = null): Collection
    {
        $ref = ($reference ? $reference->copy() : Carbon::now(self::TZ))->startOfDay();

        // Map 'm-d' => target Carbon date within the window.
        $targets = [];
        for ($i = $offsetStart; $i <= $offsetEnd; $i++) {
            $day = $ref->copy()->addDays($i);
            $targets[$day->format('m-d')] = $day;
        }

        $customers = Customer::query()
            ->active()
            ->where(function ($query) use ($targets) {
                foreach ($targets as $md => $date) {
                    [$month, $dom] = array_map('intval', explode('-', $md));

                    $query->orWhere(function ($w) use ($month, $dom) {
                        $w->whereMonth('birth_date', $month)->whereDay('birth_date', $dom);
                    });

                    // Feb-29 babies surface on Feb-28 in non-leap years.
                    if ($month === 2 && $dom === 28 && ! $date->isLeapYear()) {
                        $query->orWhere(function ($w) {
                            $w->whereMonth('birth_date', 2)->whereDay('birth_date', 29);
                        });
                    }
                }
            })
            ->orderBy('name')
            ->get();

        // Follow-up records for the reference year, keyed by customer id.
        $followups = BirthdayFollowup::query()
            ->whereIn('customer_id', $customers->pluck('id'))
            ->where('birthday_year', $ref->year)
            ->get()
            ->keyBy('customer_id');

        return $customers->map(function (Customer $customer) use ($targets, $ref, $followups) {
            $birthdayOn = $this->resolveBirthdayDate($customer, $targets, $ref);

            return [
                'customer' => $customer,
                'birthday_on' => $birthdayOn,
                'days_until' => $ref->diffInDays($birthdayOn, false),
                'is_today' => $birthdayOn->equalTo($ref),
                'followup' => $followups->get($customer->id),
            ];
        })->sortBy('days_until')->values();
    }

    private function resolveBirthdayDate(Customer $customer, array $targets, Carbon $ref): Carbon
    {
        $md = $customer->birth_date->format('m-d');

        if (isset($targets[$md])) {
            return $targets[$md]->copy();
        }

        // Feb-29 born, matched through the Feb-28 target in a non-leap year.
        if ($md === '02-29' && isset($targets['02-28'])) {
            return $targets['02-28']->copy();
        }

        // Fallback: this year's occurrence.
        return Carbon::create($ref->year, (int) $customer->birth_date->format('m'), (int) $customer->birth_date->format('d'), 0, 0, 0, self::TZ);
    }
}
