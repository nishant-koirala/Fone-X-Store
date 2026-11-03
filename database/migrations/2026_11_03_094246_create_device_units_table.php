<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_condition_id')->constrained('product_conditions')->cascadeOnDelete();
            $table->string('imei', 25)->nullable()->index();
            $table->string('serial_number', 50)->nullable()->index();
            $table->unsignedTinyInteger('battery_health')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->string('status', 20)->default('available')->index();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('trade_in_valuation_id')->nullable()->constrained('trade_in_valuations')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_units');
    }
};
