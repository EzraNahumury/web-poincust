<?php

namespace App\Domain\Points;

use App\Models\Customer;
use App\Models\PointLedger;
use App\Models\Transaction;
use App\Support\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Records a shopping transaction and its point movements as one atomic unit.
 * The backend recomputes every amount and point value; nothing from the client
 * is trusted (README §10.2).
 */
class TransactionService
{
    public function __construct(
        private readonly PointsConfig $pointsConfig,
        private readonly LedgerService $ledger,
    ) {
    }

    /**
     * @param array{
     *   customer_id:int, gross_amount:int, transaction_discount?:int,
     *   points_redeemed?:int, created_by?:int|null, transaction_date?:mixed,
     *   notes?:string|null, external_receipt_no?:string|null, idempotency_key?:string|null
     * } $data
     */
    public function record(array $data): Transaction
    {
        $gross = (int) ($data['gross_amount'] ?? 0);
        $discount = (int) ($data['transaction_discount'] ?? 0);
        $redeemed = (int) ($data['points_redeemed'] ?? 0);
        $idempotencyKey = $data['idempotency_key'] ?? null;
        $createdBy = $data['created_by'] ?? null;

        if ($gross < 0 || $discount < 0 || $redeemed < 0) {
            throw new InvalidTransactionAmountException('Nilai transaksi tidak boleh negatif.');
        }
        if ($discount > $gross) {
            throw new InvalidTransactionAmountException('Diskon transaksi tidak boleh melebihi total belanja.');
        }

        return DB::transaction(function () use ($gross, $discount, $redeemed, $idempotencyKey, $createdBy, $data) {
            // Short-circuit a resubmitted request (double-submission guard).
            if ($idempotencyKey !== null) {
                $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }

            /** @var Customer $customer */
            $customer = Customer::whereKey($data['customer_id'])->lockForUpdate()->firstOrFail();

            $calc = $this->pointsConfig->calculator();

            // Points base = gross minus transaction discount, BEFORE redemption.
            $eligible = $gross - $discount;
            $earned = $calc->pointsEarned($eligible);

            if ($redeemed > $customer->points_balance) {
                throw InsufficientPointsException::make($redeemed, $customer->points_balance);
            }

            $redemptionDiscount = $calc->redemptionValue($redeemed);
            if ($redemptionDiscount > $eligible) {
                throw RedemptionExceedsBillException::make($redemptionDiscount, $eligible);
            }

            $final = $eligible - $redemptionDiscount;

            $txn = new Transaction([
                'transaction_number' => $this->generateNumber(),
                'customer_id' => $customer->id,
                'created_by' => $createdBy,
                'transaction_date' => isset($data['transaction_date'])
                    ? Carbon::parse($data['transaction_date'])
                    : now(),
                'gross_amount' => $gross,
                'transaction_discount' => $discount,
                'eligible_amount' => $eligible,
                'points_redeemed' => $redeemed,
                'redemption_discount' => $redemptionDiscount,
                'points_earned' => $earned,
                'final_amount' => $final,
                'status' => Transaction::STATUS_COMPLETED,
                'notes' => $data['notes'] ?? null,
                'external_receipt_no' => $data['external_receipt_no'] ?? null,
                'idempotency_key' => $idempotencyKey,
            ]);
            $txn->save();

            // Redeem (debit) first, then earn (credit).
            if ($redeemed > 0) {
                $this->ledger->post($customer, PointLedger::TYPE_REDEEM, -$redeemed, [
                    'transaction_id' => $txn->id,
                    'created_by' => $createdBy,
                    'description' => "Penukaran {$redeemed} poin (diskon Rp{$redemptionDiscount})",
                    'idempotency_key' => $idempotencyKey ? "{$idempotencyKey}:REDEEM" : null,
                ]);
            }
            if ($earned > 0) {
                $this->ledger->post($customer, PointLedger::TYPE_EARN, $earned, [
                    'transaction_id' => $txn->id,
                    'created_by' => $createdBy,
                    'description' => "Perolehan {$earned} poin dari belanja Rp{$eligible}",
                    'idempotency_key' => $idempotencyKey ? "{$idempotencyKey}:EARN" : null,
                ]);
            }

            AuditLogger::log('transaction.create', [
                'actor_type' => $createdBy ? 'admin' : 'system',
                'actor_id' => $createdBy,
                'entity_type' => 'Transaction',
                'entity_id' => $txn->id,
                'after' => [
                    'transaction_number' => $txn->transaction_number,
                    'gross_amount' => $gross,
                    'transaction_discount' => $discount,
                    'eligible_amount' => $eligible,
                    'points_earned' => $earned,
                    'points_redeemed' => $redeemed,
                    'redemption_discount' => $redemptionDiscount,
                    'final_amount' => $final,
                ],
            ]);

            return $txn->refresh();
        });
    }

    private function generateNumber(): string
    {
        do {
            $number = 'TRX-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (Transaction::where('transaction_number', $number)->exists());

        return $number;
    }
}
