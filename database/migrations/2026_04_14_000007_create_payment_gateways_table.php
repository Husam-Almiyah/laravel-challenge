<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->string('driver');
            $table->integer('priority')->default(0);
            $table->string('currency')->default('SAR');
            $table->json('rules')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // Indexes for performance
            $table->index(['is_active', 'priority'], 'idx_active_priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
