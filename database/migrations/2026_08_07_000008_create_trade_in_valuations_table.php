<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_in_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('device_brand');
            $table->string('device_model');
            $table->text('condition_description');
            $table->decimal('estimated_value', 10, 2)->nullable();
            $table->enum('status', ['pending', 'reviewed', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_in_valuations');
    }
};
