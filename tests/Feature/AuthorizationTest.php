<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_guest_is_redirected_from_admin_area(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_ordinary_admin_cannot_reverse_transactions(): void
    {
        $admin = $this->adminUser(); // role: admin (no transactions.reverse)
        $customer = Customer::factory()->create();
        $txn = Transaction::create([
            'transaction_number' => 'TRX-AUTH-1',
            'customer_id' => $customer->id,
            'transaction_date' => now(),
            'gross_amount' => 100000, 'eligible_amount' => 100000, 'final_amount' => 100000,
            'points_earned' => 10, 'status' => Transaction::STATUS_COMPLETED,
        ]);

        $this->actingAs($admin, 'web')
            ->post(route('admin.transactions.reverse', $txn), ['reason' => 'coba'])
            ->assertForbidden();

        $this->assertSame(Transaction::STATUS_COMPLETED, $txn->fresh()->status);
    }

    public function test_ordinary_admin_cannot_manage_users(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin, 'web')->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_admin_can_manage_users(): void
    {
        $super = $this->superAdmin();
        $this->actingAs($super, 'web')->get(route('admin.users.index'))->assertOk();
    }

    public function test_ordinary_admin_can_record_transactions(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin, 'web')->get(route('admin.transactions.create'))->assertOk();
    }

    public function test_customer_cannot_reach_point_adjustment_endpoint(): void
    {
        $customer = Customer::factory()->create();

        // On the customer guard only; the web-guard route sees a guest.
        $this->actingAs($customer, 'customer')
            ->post(route('admin.points.adjust', $customer), ['type' => 'credit', 'points' => 999, 'reason' => 'x'])
            ->assertRedirect(route('admin.login'));

        $this->assertSame(0, $customer->fresh()->points_balance);
    }
}
