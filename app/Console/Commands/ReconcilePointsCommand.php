<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\PointLedger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Verifies that each customer's cached points_balance equals the sum of their
 * ledger deltas, and flags implausible (negative) balances. The ledger is the
 * source of truth; --fix rewrites the cached balance to match it.
 */
class ReconcilePointsCommand extends Command
{
    protected $signature = 'points:reconcile {--fix : Rewrite cached balances to match the ledger}';

    protected $description = 'Reconcile cached point balances against the ledger';

    public function handle(): int
    {
        $ledgerSums = PointLedger::query()
            ->select('customer_id', DB::raw('SUM(points_delta) as total'))
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id');

        $mismatches = 0;
        $negatives = 0;

        Customer::withTrashed()->chunkById(500, function ($customers) use ($ledgerSums, &$mismatches, &$negatives) {
            foreach ($customers as $customer) {
                $truth = (int) ($ledgerSums[$customer->id] ?? 0);

                if ($customer->points_balance !== $truth) {
                    $mismatches++;
                    $this->warn("Customer #{$customer->id} ({$customer->member_number}): cache={$customer->points_balance} ledger={$truth}");

                    if ($this->option('fix')) {
                        // Update cache only; the ledger is never changed.
                        Customer::withTrashed()->whereKey($customer->id)->update(['points_balance' => $truth]);
                    }
                }

                if ($truth < 0) {
                    $negatives++;
                    $this->error("Customer #{$customer->id} ({$customer->member_number}) has NEGATIVE ledger balance {$truth} — needs review.");
                }
            }
        });

        $this->info("Selesai. Mismatch: {$mismatches}" . ($this->option('fix') ? ' (diperbaiki)' : '') . ", saldo negatif: {$negatives}.");

        return ($mismatches === 0 && $negatives === 0) ? self::SUCCESS : self::FAILURE;
    }
}
