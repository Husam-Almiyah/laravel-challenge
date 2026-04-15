<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('cart_id')->constrained('carts')->onDelete('cascade');
            $table->ulidMorphs('itemable');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // Indexes
            $table->index(['cart_id'], 'idx_cart');
            $table->index(['itemable_type', 'itemable_id'], 'idx_itemable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
