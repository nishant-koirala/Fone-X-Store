<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('cost_total_at_purchase', 10, 2)->nullable()->after('cost_price_at_purchase');
        });

        // Safe, idempotent backfill of existing historical rows where cost_price_at_purchase was recorded as per-unit
        DB::table('order_items')
            ->whereNotNull('cost_price_at_purchase')
            ->whereNull('cost_total_at_purchase')
            ->orderBy('id')
            ->chunkById(100, function ($items): void {
                foreach ($items as $item) {
                    $total = round(((float) $item->cost_price_at_purchase) * (int) $item->quantity, 2);

                    DB::table('order_items')
                        ->where('id', $item->id)
                        ->update([
                            'cost_total_at_purchase' => $total,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('cost_total_at_purchase');
        });
    }
};
