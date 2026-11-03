<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Clean any legacy placeholder strings ('Trade-In Request Submission') from customer addresses.
     */
    public function up(): void
    {
        DB::table('customers')
            ->where('address', 'Trade-In Request Submission')
            ->update(['address' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: do not restore placeholder addresses
    }
};
