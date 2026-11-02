<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add soft-delete support to the orders table.
     *
     * WHY: Filament's DeleteAction previously hard-deleted orders, removing
     * both the order row and its order_items permanently. Adding deleted_at
     * lets staff recover accidentally deleted orders via RestoreAction, and
     * keeps historical data intact for reporting.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->softDeletes(); // adds nullable deleted_at timestamp column
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
