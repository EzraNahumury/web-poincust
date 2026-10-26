<?php

namespace App\Domain\Points;

use App\Models\Customer;
use App\Models\PointLedger;
use App\Models\Transaction;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Reverses a completed transaction WITHOUT deleting history. The original row
 * is kept for audit; offsetting REVERSAL ledger entries undo the point effect
 * (README §11).
 */
class ReversalService
{
    public function __construct(private readonly LedgerService $ledger)
    {
    }

    public function reverse(Transaction $transaction, User $actor, string $reason): Transaction
    {
        return DB::transaction(function () use ($transaction, $actor, $reason) {
            /** @var Transaction $txn */
            $txn = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($txn->isReversed()) {
                throw TransactionAlreadyReversedException::make($txn->transaction_number);
            }

            /** @var Customer $customer */
            $customer = Customer::whereKey($txn->customer_id)->lockForUpdate()->firstOrFail();

            $before = ['status' => $txn->status, 'balance' => $customer->points_balance];

            // Undo points EARNED (debit them back out). This may push the cached
            // balance negative if the customer already spent them — that is
            // allowed and surfaced for admin review; history is never deleted.
            if ($txn->points_earned > 0) {
                $this->ledger->post($customer, PointLedger::TYPE_REVERSAL, -$txn->points_earned, [
                    'transaction_id' => $txn->id,
                    'created_by' => $actor->id,
                    'description' => "Pembalikan perolehan {$txn->points_earned} poin (transaksi {$txn->transaction_number})",
                ]);
            }

            // Return points REDEEMED back to the customer (credit).
            if ($txn->points_redeemed > 0) {
                $this->ledger->post($customer, PointLedger::TYPE_REVERSAL, $txn->points_redeemed, [
                    'transaction_id' => $txn->id,
                    'created_by' => $actor->id,
                    'description' => "Pengembalian {$txn->points_redeemed} poin dari pembatalan (transaksi {$txn->transaction_number})",
                ]);
            }

            $txn->status = Transaction::STATUS_REVERSED;
            $txn->reversed_by = $actor->id;
            $txn->reversed_at = now();
            $txn->reversal_reason = $reason;
            $txn->save();

            AuditLogger::log('transaction.reverse', [
                'actor_type' => 'admin',
                'actor_id' => $actor->id,
                'entity_type' => 'Transaction',
                'entity_id' => $txn->id,
                'reason' => $reason,
                'before' => $before,
                'after' => ['status' => $txn->status, 'balance' => $customer->points_balance],
            ]);

            return $txn->refresh();
        });
    }
}
