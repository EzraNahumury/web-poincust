<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();

            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            // Admin/cashier who recorded it. Null = system.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('transaction_date');

            // All money is stored as integer rupiah. NEVER floating point.
            $table->unsignedBigInteger('gross_amount');            // before any discount
            $table->unsignedBigInteger('transaction_discount')->default(0);
            $table->unsignedBigInteger('eligible_amount');         // gross - transaction_discount (points base)
            $table->unsignedInteger('points_redeemed')->default(0);
            $table->unsignedBigInteger('redemption_discount')->default(0); // points_redeemed * value
            $table->unsignedInteger('points_earned')->default(0);
            $table->unsignedBigInteger('final_amount');            // eligible - redemption_discount

            $table->string('status')->default('completed'); // completed | reversed
            $table->text('notes')->nullable();
            $table->string('external_receipt_no')->nullable();

            // Reversal linkage.
            $table->foreignId('reversed_transaction_id')->nullable()
                  ->constrained('transactions')->nullOnDelete();
            $table->text('reversal_reason')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();

            // Prevents duplicate transactions from resubmitted requests.
            $table->string('idempotency_key')->nullable()->unique();

            $table->timestamps();

            $table->index(['customer_id', 'transaction_date']);
            $table->index('status');
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
