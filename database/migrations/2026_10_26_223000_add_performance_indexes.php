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
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_visible');
            $table->index('is_featured');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('product_conditions', function (Blueprint $table) {
            $table->index('quantity_in_stock');
        });

        Schema::table('trade_in_valuations', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_in_valuations', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('product_conditions', function (Blueprint $table) {
            $table->dropIndex(['quantity_in_stock']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_visible']);
            $table->dropIndex(['is_featured']);
        });
    }
};
