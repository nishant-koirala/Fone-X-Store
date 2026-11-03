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
        Schema::table('product_conditions', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('cost_price_at_purchase', 10, 2)->nullable()->after('price_at_purchase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_conditions', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('cost_price_at_purchase');
        });
    }
};
