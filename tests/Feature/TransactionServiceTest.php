<?php

namespace Tests\Feature;

use App\Domain\Points\AdjustmentService;
use App\Domain\Points\InsufficientPointsException;
use App\Domain\Points\RedemptionExceedsBillException;
use App\Domain\Points\ReversalService;
use App\Domain\Points\TransactionAlreadyReversedException;
use App\Domain\Points\TransactionService;
use App\Models\Customer;
use App\Models\PointLedger;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;
    private ReversalService $reversals;
    private AdjustmentService $adjustments;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        $this->service = app(TransactionService::class);
        $this->reversals = app(ReversalService::class);
        $this->adjustments = app(AdjustmentService::class);
        $this->admin = $this->superAdmin();
    }

    private function giveBalance(Customer $customer, int $points): void
    {
        $this->adjustments->credit($customer, $points, 'Saldo awal untuk pengujian', $this->admin);
    }

    public function test_records_transaction_and_earns_floored_points(): void
    {
        $customer = Customer::factory()->create();

        $txn = $this->service->record([
            'customer_id' => $customer->id,
            'gross_amount' => 275000,
            'created_by' => $this->admin->id,
        ]);

        $this->assertSame(27, $txn->points_earned);
        $this->assertSame(275000, $txn->eligible_amount);
        $this->assertSame(275000, $txn->final_amount);
        $this->assertSame(27, $customer->fresh()->points_balance);
        $this->assertDatabaseHas('point_ledger', [
            'customer_id' => $customer->id, 'type' => 'EARN', 'points_delta' => 27,
        ]);
    }

    public function test_sub_threshold_amount_earns_zero_points_and_no_ledger_row(): void
    {
        $customer = Customer::factory()->create();

        $txn = $this->service->record([
            'customer_id' => $customer->id,
            'gross_amount' => 9000,
            'created_by' => $this->admin->id,
        ]);

        $this->assertSame(0, $txn->points_earned);
        $this->assertSame(0, PointLedger::where('customer_id', $customer->id)->where('type', 'EARN')->count());
    }

    public function test_redemption_reduces_bill_and_balance_correctly(): void
    {
        $customer = Customer::factory()->create();
        $this->giveBalance($customer, 100);

        $txn = $this->service->record([
            'customer_id' => $customer->id,
            'gross_amount' => 275000,
            'points_redeemed' => 20,
            'created_by' => $this->admin->id,
        ]);

        $this->assertSame(10000, $txn->redemption_discount);
        $this->assertSame(265000, $txn->final_amount);
        $this->assertSame(27, $txn->points_earned);
        // 100 - 20 + 27 = 107
        $this->assertSame(107, $customer->fresh()->points_balance);
    }

    public function test_redeeming_more_than_balance_is_rejected(): void
    {
        $customer = Customer::factory()->create();
        $this->giveBalance($customer, 50);

        $this->expectException(InsufficientPointsException::class);
        $this->service->record([
            'customer_id' => $customer->id,
            'gross_amount' => 275000,
            'points_redeemed' => 100,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_redemption_discount_cannot_exceed_bill(): void
    {
        $customer = Customer::factory()->create();
        $this->giveBalance($customer, 100);

        $this->expectException(RedemptionExceedsBillException::class);
        // 20 points = Rp10.000 discount, but eligible bill is only Rp5.000.
        $this->service->record([
            'customer_id' => $customer->id,
            'gross_amount' => 5000,
            'points_redeemed' => 20,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_idempotency_key_prevents_double_posting(): void
    {
        $customer = Customer::factory()->create();

        $first = $this->service->record([
            'customer_id' => $customer->id,
            'gross_amount' => 100000,
            'created_by' => $this->admin->id,
            'idempotency_key' => 'abc-123',
        ]);

        $second = $this->service->record([
            'customer_id' => $customer->id,
            'gross_amount' => 100000,
            'created_by' => $this->admin->id,
            'idempotency_key' => 'abc-123',
        ]);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Transaction::count());
        $this->assertSame(10, $customer->fresh()->points_balance); // counted once
    }

    public function test_reversal_undoes_points_and_keeps_history(): void
    {
        $customer = Customer::factory()->create();

        $txn = $this->service->record([
            'customer_id' => $customer->id,
            'gross_amount' => 275000,
            'created_by' => $this->admin->id,
        ]);
        $this->assertSame(27, $customer->fresh()->points_balance);

        $this->reversals->reverse($txn, $this->admin, 'Salah input');

        $this->assertSame(0, $customer->fresh()->points_balance);
        $this->assertSame(Transaction::STATUS_REVERSED, $txn->fresh()->status);
        // Original transaction still exists for audit.
        $this->assertDatabaseHas('transactions', ['id' => $txn->id]);
        $this->assertDatabaseHas('point_ledger', ['transaction_id' => $txn->id, 'type' => 'REVERSAL', 'points_delta' => -27]);
    }

    public function test_reversal_returns_redeemed_points(): void
    {
        $customer = Customer::factory()->create();
        $this->giveBalance($customer, 100);

        $txn = $this->service->record([
            'customer_id' => $customer->id,
            'gross_amount' => 275000,
            'points_redeemed' => 20,
            'created_by' => $this->admin->id,
        ]);
        $this->assertSame(107, $customer->fresh()->points_balance);

        $this->reversals->reverse($txn, $this->admin, 'Komplain customer');

        // Back to the pre-transaction balance of 100.
        $this->assertSame(100, $customer->fresh()->points_balance);
    }

    public function test_cannot_reverse_twice(): void
    {
        $customer = Customer::factory()->create();
        $txn = $this->service->record([
            'customer_id' => $customer->id, 'gross_amount' => 100000, 'created_by' => $this->admin->id,
        ]);

        $this->reversals->reverse($txn, $this->admin, 'pertama');

        $this->expectException(TransactionAlreadyReversedException::class);
        $this->reversals->reverse($txn->fresh(), $this->admin, 'kedua');
    }

    public function test_cached_balance_always_matches_ledger(): void
    {
        $customer = Customer::factory()->create();
        $this->giveBalance($customer, 50);

        $this->service->record(['customer_id' => $customer->id, 'gross_amount' => 123456, 'created_by' => $this->admin->id]);
        $t2 = $this->service->record(['customer_id' => $customer->id, 'gross_amount' => 275000, 'points_redeemed' => 10, 'created_by' => $this->admin->id]);
        $this->reversals->reverse($t2, $this->admin, 'koreksi');

        $ledgerSum = (int) PointLedger::where('customer_id', $customer->id)->sum('points_delta');
        $this->assertSame($ledgerSum, $customer->fresh()->points_balance);
    }
}
