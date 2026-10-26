<?php

namespace Tests\Feature;

use App\Domain\Auth\PinHasher;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Andi Pratama',
            'birth_date' => '1995-05-20',
            'whatsapp_number' => '081234567890',
            'username' => 'andi.p',
            'email' => 'andi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'pin' => '135790',
            'pin_confirmation' => '135790',
            'terms' => '1',
        ], $overrides);
    }

    public function test_customer_can_register_with_valid_data(): void
    {
        $response = $this->post(route('member.register.store'), $this->validPayload());

        $response->assertRedirect(route('member.dashboard'));
        $this->assertAuthenticated('customer');

        $customer = Customer::first();
        $this->assertNotNull($customer);
        $this->assertSame('andi.p', $customer->username);
        // Indonesian number normalised to international form.
        $this->assertSame('6281234567890', $customer->whatsapp_number);
        $this->assertStringStartsWith('AVA', $customer->member_number);
    }

    public function test_password_and_pin_are_hashed_not_plaintext(): void
    {
        $this->post(route('member.register.store'), $this->validPayload());
        $customer = Customer::first();

        $this->assertNotSame('rahasia123', $customer->password);
        $this->assertNotSame('135790', $customer->pin_hash);
        $this->assertTrue(Hash::check('rahasia123', $customer->password));
        $this->assertTrue(app(PinHasher::class)->check('135790', $customer->pin_hash));
    }

    public function test_duplicate_username_is_rejected_case_insensitively(): void
    {
        Customer::factory()->create(['username' => 'andi.p']);

        $response = $this->post(route('member.register.store'), $this->validPayload([
            'username' => 'ANDI.P', // different case, must still clash
            'whatsapp_number' => '081200000000',
        ]));

        $response->assertSessionHasErrors('username');
        $this->assertSame(1, Customer::where('username', 'andi.p')->count());
    }

    public function test_duplicate_whatsapp_is_rejected(): void
    {
        Customer::factory()->create(['whatsapp_number' => '6281234567890']);

        $response = $this->post(route('member.register.store'), $this->validPayload());

        $response->assertSessionHasErrors('whatsapp_number');
    }

    public function test_pin_must_be_six_digits_and_confirmed(): void
    {
        $this->post(route('member.register.store'), $this->validPayload(['pin' => '123', 'pin_confirmation' => '123']))
            ->assertSessionHasErrors('pin');

        $this->post(route('member.register.store'), $this->validPayload(['pin_confirmation' => '999999']))
            ->assertSessionHasErrors('pin');
    }

    public function test_terms_must_be_accepted(): void
    {
        $this->post(route('member.register.store'), $this->validPayload(['terms' => null]))
            ->assertSessionHasErrors('terms');
    }
}
