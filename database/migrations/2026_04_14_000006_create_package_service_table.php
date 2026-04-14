<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_service', function (Blueprint $table) {
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->unique(['package_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_service');
    }
};
