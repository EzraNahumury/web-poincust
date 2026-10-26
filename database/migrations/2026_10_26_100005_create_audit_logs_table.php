<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type')->nullable();   // admin | customer | system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');                    // e.g. transaction.create
            $table->string('entity_type')->nullable();   // e.g. Transaction
            $table->unsignedBigInteger('entity_id')->nullable();
            // NEVER store passwords, PINs, tokens or other credentials here.
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->string('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['actor_type', 'actor_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
