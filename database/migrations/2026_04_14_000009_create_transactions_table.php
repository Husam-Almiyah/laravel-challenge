<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulidMorphs('payer');
            $table->ulidMorphs('payable');
            $table->foreignUlid('gateway_id')->nullable()->constrained('payment_gateways')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('SAR');
            $table->string('status')->default('pending')->index();
            $table->string('reference')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index(['payer_type', 'payer_id'], 'idx_payer');
            $table->index(['payable_type', 'payable_id'], 'idx_payable');
            $table->index(['gateway_id', 'status'], 'idx_gateway_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
