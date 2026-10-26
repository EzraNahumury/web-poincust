<?php

namespace Tests\Feature;

use App\Domain\Auth\PinHasher;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        config(['pin.max_attempts' => 3, 'pin.lock_minutes' => 15]);
    }

    public function test_customer_can_change_pin_with_correct_current_pin(): void
    {
        $customer = Customer::factory()->create(); // factory PIN = 123456

        $this->actingAs($customer, 'customer')->put(route('member.security.pin'), [
            'current_pin' => '123456',
            'pin' => '654321',
            'pin_confirmation' => '654321',
        ])->assertRedirect(route('member.security'));

        $this->assertTrue(app(PinHasher::class)->check('654321', $customer->fresh()->pin_hash));
    }

    public function test_wrong_current_pin_is_rejected(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer, 'customer')->put(route('member.security.pin'), [
            'current_pin' => '000000',
            'pin' => '654321',
            'pin_confirmation' => '654321',
        ])->assertSessionHasErrors('current_pin');

        // Old PIN still valid.
        $this->assertTrue(app(PinHasher::class)->check('123456', $customer->fresh()->pin_hash));
    }

    public function test_pin_locks_after_too_many_failed_attempts(): void
    {
        $customer = Customer::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($customer, 'customer')->put(route('member.security.pin'), [
                'current_pin' => '111111',
                'pin' => '654321',
                'pin_confirmation' => '654321',
            ]);
        }

        $this->assertTrue($customer->fresh()->isPinLocked());

        // Even the correct PIN is refused while locked.
        $this->actingAs($customer, 'customer')->put(route('member.security.pin'), [
            'current_pin' => '123456',
            'pin' => '777777',
            'pin_confirmation' => '777777',
        ])->assertSessionHasErrors('current_pin');
    }
}
