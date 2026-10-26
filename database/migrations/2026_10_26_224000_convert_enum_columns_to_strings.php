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
            $table->string('grade', 20)->default('new')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });

        Schema::table('trade_in_valuations', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('type', 30)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting enum changes across database engines
    }
};
