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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            $table->string('shipping_name')->nullable()->after('total');
            $table->string('shipping_phone')->nullable()->after('shipping_name');
            $table->string('shipping_email')->nullable()->after('shipping_phone');
            $table->text('shipping_address')->nullable()->after('shipping_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'shipping_name',
                'shipping_phone',
                'shipping_email',
                'shipping_address',
            ]);
        });
    }
};
