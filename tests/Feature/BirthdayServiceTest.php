<?php

namespace Tests\Feature;

use App\Domain\Birthday\BirthdayService;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BirthdayServiceTest extends TestCase
{
    use RefreshDatabase;

    private BirthdayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BirthdayService::class);
    }

    public function test_birthday_today_matches_month_and_day_ignoring_year(): void
    {
        $ref = Carbon::create(2026, 6, 15, 0, 0, 0, BirthdayService::TZ);
        $customer = Customer::factory()->birthDate('1988-06-15')->create();
        Customer::factory()->birthDate('1990-07-01')->create(); // not today

        $today = $this->service->today($ref);

        $this->assertCount(1, $today);
        $this->assertSame($customer->id, $today->first()['customer']->id);
        $this->assertTrue($today->first()['is_today']);
    }

    public function test_upcoming_lists_birthdays_within_window(): void
    {
        $ref = Carbon::create(2026, 6, 15, 0, 0, 0, BirthdayService::TZ);
        Customer::factory()->birthDate('2000-06-18')->create();  // in 3 days
        Customer::factory()->birthDate('2000-06-30')->create();  // out of 7-day window

        $upcoming = $this->service->upcoming(7, $ref);

        $this->assertCount(1, $upcoming);
        $this->assertSame(3, (int) $upcoming->first()['days_until']);
    }

    public function test_feb_29_birthday_shows_on_feb_28_in_non_leap_year(): void
    {
        $this->assertFalse(Carbon::create(2025, 1, 1)->isLeapYear());

        $ref = Carbon::create(2025, 2, 28, 0, 0, 0, BirthdayService::TZ);
        $customer = Customer::factory()->birthDate('2000-02-29')->create();

        $today = $this->service->today($ref);

        $this->assertCount(1, $today);
        $this->assertSame($customer->id, $today->first()['customer']->id);
    }

    public function test_feb_29_birthday_shows_on_feb_29_in_leap_year(): void
    {
        $this->assertTrue(Carbon::create(2024, 1, 1)->isLeapYear());

        $ref = Carbon::create(2024, 2, 29, 0, 0, 0, BirthdayService::TZ);
        $customer = Customer::factory()->birthDate('2000-02-29')->create();

        $this->assertCount(1, $this->service->today($ref));
    }

    public function test_inactive_customers_are_excluded(): void
    {
        $ref = Carbon::create(2026, 6, 15, 0, 0, 0, BirthdayService::TZ);
        Customer::factory()->inactive()->birthDate('1988-06-15')->create();

        $this->assertCount(0, $this->service->today($ref));
    }
}
