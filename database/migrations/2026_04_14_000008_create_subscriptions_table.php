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
            $table->index('ends_at', 'idx_subscriptions_ends_at');
            $table->index(['user_id', 'status', 'is_trial'], 'idx_user_status_trial');
        });

        // Add generated column and unique index in separate block to satisfy MySQL/MariaDB requirements
        // We use VIRTUAL instead of STORED because STORED columns cannot reference columns involved in cascading foreign keys.
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->char('active_user_id', 26)
                ->nullable()
                ->virtualAs("CASE WHEN status = 'active' THEN user_id ELSE NULL END")
                ->unique('uk_subscriptions_active_user')
                ->after('is_trial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
