<?php

namespace Database\Factories;

use App\Domain\Auth\PinHasher;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'member_number' => 'AVA' . str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => fake()->name(),
            'birth_date' => fake()->dateTimeBetween('-50 years', '-17 years')->format('Y-m-d'),
            'whatsapp_number' => '628' . fake()->unique()->numerify('##########'),
            'username' => Str::lower(Str::random(10)),
            'email' => fake()->optional()->safeEmail(),
            'password' => Hash::make('password123'),
            'pin_hash' => app(PinHasher::class)->make('123456'),
            'points_balance' => 0,
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }

    public function birthDate(string $date): static
    {
        return $this->state(fn () => ['birth_date' => $date]);
    }
}
