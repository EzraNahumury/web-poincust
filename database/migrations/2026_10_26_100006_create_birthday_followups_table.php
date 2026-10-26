<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            // The calendar year of the birthday being followed up.
            $table->year('birthday_year');
            $table->string('status')->default('pending'); // pending | contacted
            $table->foreignId('followed_up_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('followed_up_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            // One follow-up record per customer per year.
            $table->unique(['customer_id', 'birthday_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_followups');
    }
};
