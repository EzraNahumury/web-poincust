<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('member_number')->unique();

            $table->string('name');
            $table->date('birth_date');

            // Stored normalised to international format, e.g. 6281234567890.
            // One WhatsApp number = one account (documented business decision).
            $table->string('whatsapp_number', 20)->unique();

            // Stored already normalised (trimmed + lowercased) to prevent
            // case-variant duplicates.
            $table->string('username')->unique();
            $table->string('email')->nullable();

            // Secrets: only ever stored hashed. PIN uses a separate Argon2id hash.
            $table->string('password');
            $table->string('pin_hash');

            // PIN brute-force protection.
            $table->unsignedTinyInteger('pin_attempts')->default(0);
            $table->timestamp('pin_locked_until')->nullable();

            // Cached balance, kept in sync atomically with the point_ledger
            // inside the same DB transaction. Reconcilable via SUM(points_delta).
            $table->integer('points_balance')->default(0);

            $table->string('status')->default('active'); // active | inactive

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('birth_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
