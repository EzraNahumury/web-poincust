<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The point_ledger is the SOURCE OF TRUTH for every point movement.
        // Rows are append-only / immutable: never updated or deleted through
        // normal business flow. Corrections are made by posting new rows.
        Schema::create('point_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();

            // EARN | REDEEM | REVERSAL | ADJUSTMENT_CREDIT | ADJUSTMENT_DEBIT | EXPIRED
            $table->string('type', 32);

            // Signed: positive = credit, negative = debit.
            $table->integer('points_delta');
            // Running balance immediately after this entry was posted.
            $table->integer('balance_after');

            $table->string('description')->nullable();
            // Admin who caused it; null for system-generated entries.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            // For REVERSAL rows: the ledger entry being reversed.
            $table->foreignId('reversal_of_id')->nullable()->constrained('point_ledger')->nullOnDelete();

            // Guards against double-posting on retried requests.
            $table->string('idempotency_key')->nullable()->unique();

            // Immutable => only a creation timestamp, no updated_at.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['customer_id', 'id']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_ledger');
    }
};
