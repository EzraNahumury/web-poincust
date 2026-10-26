<?php

namespace App\Domain\Membership;

use App\Domain\Auth\PinHasher;
use App\Models\Customer;
use App\Support\AuditLogger;
use App\Support\WhatsApp;
use Illuminate\Support\Facades\DB;

/**
 * Creates a member account with normalised identifiers, a generated member
 * number, a hashed login password and a separately-hashed PIN. Used by both
 * public self-registration and admin-side creation.
 */
class CustomerRegistrar
{
    public function __construct(private readonly PinHasher $pinHasher)
    {
    }

    /**
     * @param array{
     *   name:string, birth_date:mixed, whatsapp_number:string, username:string,
     *   email?:string|null, password:string, pin:string, status?:string
     * } $data
     */
    public function register(array $data, string $actorType = 'customer', ?int $actorId = null): Customer
    {
        return DB::transaction(function () use ($data, $actorType, $actorId) {
            $customer = new Customer([
                'member_number' => $this->generateMemberNumber(),
                'name' => $data['name'],
                'birth_date' => $data['birth_date'],
                'whatsapp_number' => WhatsApp::normalize($data['whatsapp_number']),
                'username' => Customer::normalizeUsername($data['username']),
                'email' => $data['email'] ?? null,
                'password' => $data['password'], // 'hashed' cast hashes on set
                'status' => $data['status'] ?? 'active',
            ]);
            // PIN uses the dedicated Argon2id hasher, never the password cast.
            $customer->pin_hash = $this->pinHasher->make($data['pin']);
            $customer->save();

            AuditLogger::log('customer.register', [
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'entity_type' => 'Customer',
                'entity_id' => $customer->id,
                'after' => [
                    'member_number' => $customer->member_number,
                    'username' => $customer->username,
                ],
            ]);

            return $customer;
        });
    }

    private function generateMemberNumber(): string
    {
        do {
            $number = 'AVA' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Customer::withTrashed()->where('member_number', $number)->exists());

        return $number;
    }
}
