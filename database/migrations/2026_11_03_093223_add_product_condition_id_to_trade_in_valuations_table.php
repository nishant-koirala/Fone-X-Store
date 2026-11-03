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
        Schema::table('trade_in_valuations', function (Blueprint $table) {
            $table->foreignId('product_condition_id')->nullable()->after('customer_id')->constrained('product_conditions')->nullOnDelete();
            $table->timestamp('stock_ingested_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_in_valuations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_condition_id');
            $table->dropColumn('stock_ingested_at');
        });
    }
};
