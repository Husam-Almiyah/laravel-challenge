<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->ulidMorphs('itemable');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // Indexes
            $table->index(['booking_id'], 'idx_booking_items_booking');
            $table->index(['itemable_type', 'itemable_id'], 'idx_booking_items_itemable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
