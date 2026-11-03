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
            $table->boolean('tracks_units')->default(false)->after('cost_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_conditions', function (Blueprint $table) {
            $table->dropColumn('tracks_units');
        });
    }
};
