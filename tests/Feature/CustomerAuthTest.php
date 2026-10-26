<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_customer_can_login_with_username_and_password(): void
    {
        $customer = Customer::factory()->create([
            'username' => 'rina',
            'password' => Hash::make('secret123'),
        ]);

        $this->post(route('member.login.store'), ['username' => 'rina', 'password' => 'secret123'])
            ->assertRedirect(route('member.dashboard'));

        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_inactive_customer_cannot_login(): void
    {
        Customer::factory()->inactive()->create([
            'username' => 'nonaktif',
            'password' => Hash::make('secret123'),
        ]);

        $this->post(route('member.login.store'), ['username' => 'nonaktif', 'password' => 'secret123'])
            ->assertSessionHasErrors('username');
        $this->assertGuest('customer');
    }

    public function test_guest_cannot_access_member_dashboard(): void
    {
        $this->get(route('member.dashboard'))->assertRedirect(route('member.login'));
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer, 'customer')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_view_another_customers_transaction(): void
    {
        $owner = Customer::factory()->create();
        $other = Customer::factory()->create();

        $txn = Transaction::create([
            'transaction_number' => 'TRX-TEST-1',
            'customer_id' => $owner->id,
            'transaction_date' => now(),
            'gross_amount' => 50000,
            'eligible_amount' => 50000,
            'final_amount' => 50000,
            'points_earned' => 5,
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        // The owner can see it...
        $this->actingAs($owner, 'customer')->get(route('member.transactions.show', $txn))->assertOk();

        // ...another customer gets a 404 (IDOR protection).
        $this->actingAs($other, 'customer')->get(route('member.transactions.show', $txn))->assertNotFound();
    }
}
