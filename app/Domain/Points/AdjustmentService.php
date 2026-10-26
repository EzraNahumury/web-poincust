<?php

namespace App\Domain\Points;

use App\Models\Customer;
use App\Models\PointLedger;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Manual, audited point adjustments by an authorised admin (README §12).
 * Every adjustment requires a reason and lands in the ledger + audit log.
 */
class AdjustmentService
{
    public function __construct(private readonly LedgerService $ledger)
    {
    }

    public function credit(Customer $customer, int $points, string $reason, User $actor): PointLedger
    {
        return $this->apply($customer, PointLedger::TYPE_ADJUSTMENT_CREDIT, abs($points), $reason, $actor);
    }

    public function debit(Customer $customer, int $points, string $reason, User $actor): PointLedger
    {
        return $this->apply($customer, PointLedger::TYPE_ADJUSTMENT_DEBIT, -abs($points), $reason, $actor);
    }

    private function apply(Customer $customer, string $type, int $delta, string $reason, User $actor): PointLedger
    {
        if ($delta === 0) {
            throw new InvalidTransactionAmountException('Jumlah penyesuaian poin tidak boleh nol.');
        }
        if (trim($reason) === '') {
            throw new InvalidTransactionAmountException('Penyesuaian poin wajib disertai alasan.');
        }

        return DB::transaction(function () use ($customer, $type, $delta, $reason, $actor) {
            /** @var Customer $locked */
            $locked = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();

            // A debit may not drive the balance negative.
            if ($delta < 0 && abs($delta) > $locked->points_balance) {
                throw InsufficientPointsException::make(abs($delta), $locked->points_balance);
            }

            $before = ['balance' => $locked->points_balance];

            $entry = $this->ledger->post($locked, $type, $delta, [
                'created_by' => $actor->id,
                'description' => $reason,
            ]);

            AuditLogger::log('points.adjust', [
                'actor_type' => 'admin',
                'actor_id' => $actor->id,
                'entity_type' => 'Customer',
                'entity_id' => $locked->id,
                'reason' => $reason,
                'before' => $before,
                'after' => ['balance' => $locked->points_balance, 'delta' => $delta, 'type' => $type],
            ]);

            return $entry;
        });
    }
}
