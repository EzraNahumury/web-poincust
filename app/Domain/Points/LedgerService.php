<?php

namespace App\Domain\Points;

use App\Models\Customer;
use App\Models\PointLedger;

/**
 * Appends immutable ledger rows and keeps the customer's cached balance in
 * lock-step. Every method MUST be called inside a DB transaction with the
 * customer row already locked (SELECT ... FOR UPDATE) by the caller, so that
 * concurrent transactions cannot double-spend or drive the balance negative.
 */
class LedgerService
{
    /**
     * @param array{
     *   transaction_id?:int|null, created_by?:int|null, description?:string|null,
     *   reversal_of_id?:int|null, idempotency_key?:string|null
     * } $opts
     */
    public function post(Customer $customer, string $type, int $delta, array $opts = []): PointLedger
    {
        $newBalance = $customer->points_balance + $delta;

        $entry = new PointLedger([
            'customer_id' => $customer->id,
            'transaction_id' => $opts['transaction_id'] ?? null,
            'type' => $type,
            'points_delta' => $delta,
            'balance_after' => $newBalance,
            'description' => $opts['description'] ?? null,
            'created_by' => $opts['created_by'] ?? null,
            'reversal_of_id' => $opts['reversal_of_id'] ?? null,
            'idempotency_key' => $opts['idempotency_key'] ?? null,
        ]);
        $entry->save();

        // Update the cached balance atomically within the same locked tx.
        $customer->points_balance = $newBalance;
        $customer->save();

        return $entry;
    }

    /** Authoritative balance computed straight from the ledger. */
    public function computeBalance(int $customerId): int
    {
        return (int) PointLedger::where('customer_id', $customerId)->sum('points_delta');
    }
}
