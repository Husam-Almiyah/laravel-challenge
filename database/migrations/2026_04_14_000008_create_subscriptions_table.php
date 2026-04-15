<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUlid('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status')->default('pending')->index();
            $table->boolean('is_trial')->default(false);
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status'], 'idx_user_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
