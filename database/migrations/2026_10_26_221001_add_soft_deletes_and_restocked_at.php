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
            $table->softDeletes();
        });

        Schema::table('product_conditions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('restocked_at')->nullable()->after('status');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_condition_id']);
            $table->foreign('product_condition_id')
                ->references('id')
                ->on('product_conditions')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_condition_id']);
            $table->foreign('product_condition_id')
                ->references('id')
                ->on('product_conditions')
                ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('restocked_at');
        });

        Schema::table('product_conditions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
