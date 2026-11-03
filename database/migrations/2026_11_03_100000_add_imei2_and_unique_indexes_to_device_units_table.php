<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('device_units', function (Blueprint $table) {
            $table->string('imei2', 25)->nullable()->after('imei')->index();
        });

        // Check for duplicates before attempting unique index creation
        /** @var array<string> $imeiDuplicates */
        $imeiDuplicates = DB::table('device_units')
            ->whereNotNull('imei')
            ->groupBy('imei')
            ->havingRaw('count(*) > 1')
            ->pluck('imei')
            ->all();

        /** @var array<string> $imei2Duplicates */
        $imei2Duplicates = DB::table('device_units')
            ->whereNotNull('imei2')
            ->groupBy('imei2')
            ->havingRaw('count(*) > 1')
            ->pluck('imei2')
            ->all();

        /** @var array<string> $crossDuplicates */
        $crossDuplicates = DB::table('device_units')
            ->whereNotNull('imei')
            ->whereIn('imei', function ($query) {
                $query->select('imei2')->from('device_units')->whereNotNull('imei2');
            })
            ->pluck('imei')
            ->all();

        $hasDuplicates = ! empty($imeiDuplicates) || ! empty($imei2Duplicates) || ! empty($crossDuplicates);

        if ($hasDuplicates) {
            Log::warning('Skipping unique index creation on device_units due to duplicate IMEIs found:', [
                'imei_duplicates' => $imeiDuplicates,
                'imei2_duplicates' => $imei2Duplicates,
                'cross_duplicates' => $crossDuplicates,
            ]);
        } else {
            Schema::table('device_units', function (Blueprint $table) {
                $table->unique('imei');
                $table->unique('imei2');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_units', function (Blueprint $table) {
            try {
                $table->dropUnique(['imei']);
            } catch (Throwable $e) {
                // Ignore if unique index was not created
            }

            try {
                $table->dropUnique(['imei2']);
            } catch (Throwable $e) {
                // Ignore if unique index was not created
            }

            $table->dropColumn('imei2');
        });
    }
};
