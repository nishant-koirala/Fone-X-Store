<?php

namespace App\Console\Commands;

use App\Mail\InventoryReconciliationReport;
use App\Models\ProductCondition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReconcileInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile stock quantities against available device units and stock movement history (report only)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting inventory reconciliation audit...');

        $conditions = ProductCondition::with(['product', 'availableDeviceUnits', 'stockMovements'])->get();

        $mismatches = [];

        foreach ($conditions as $condition) {
            $stock = (int) $condition->quantity_in_stock;
            $movementSum = (int) $condition->stockMovements->sum('quantity');
            $productName = $condition->product ? "{$condition->product->brand} {$condition->product->name}" : 'Unknown Product';
            $grade = $condition->grade->value;

            if ($condition->tracks_units) {
                $availableUnits = $condition->availableDeviceUnits->count();

                $hasDiscrepancy = ($stock !== $availableUnits) || ($stock !== $movementSum);

                if ($hasDiscrepancy) {
                    $reasons = [];
                    if ($stock !== $availableUnits) {
                        $reasons[] = "Stock ({$stock}) != Units ({$availableUnits})";
                    }
                    if ($stock !== $movementSum) {
                        $reasons[] = "Stock ({$stock}) != Movements ({$movementSum})";
                    }

                    $mismatches[] = [
                        'id' => $condition->id,
                        'product' => $productName,
                        'grade' => $grade,
                        'tracked' => true,
                        'stock' => $stock,
                        'units' => $availableUnits,
                        'movements' => $movementSum,
                        'discrepancy' => implode(', ', $reasons),
                    ];
                }
            } else {
                $hasDiscrepancy = ($stock !== $movementSum);

                if ($hasDiscrepancy) {
                    $mismatches[] = [
                        'id' => $condition->id,
                        'product' => $productName,
                        'grade' => $grade,
                        'tracked' => false,
                        'stock' => $stock,
                        'units' => 'N/A',
                        'movements' => $movementSum,
                        'discrepancy' => "Stock ({$stock}) != Movements ({$movementSum})",
                    ];
                }
            }
        }

        if (empty($mismatches)) {
            $this->info("Inventory audit clean: all {$conditions->count()} condition(s) reconciled with zero drift.");

            return Command::SUCCESS;
        }

        $this->error('Inventory drift detected! '.count($mismatches).' condition(s) have discrepancies.');

        $tableRows = array_map(function ($m) {
            return [
                $m['id'],
                $m['product'],
                $m['grade'],
                $m['tracked'] ? 'Yes' : 'No',
                $m['stock'],
                $m['units'],
                $m['movements'],
                $m['discrepancy'],
            ];
        }, $mismatches);

        $this->table(
            ['ID', 'Product', 'Grade', 'Tracked', 'Stock Qty', 'Avail Units', 'Movement Sum', 'Discrepancy'],
            $tableRows
        );

        Log::warning('Inventory reconciliation found mismatches', [
            'count' => count($mismatches),
            'mismatches' => $mismatches,
        ]);

        $adminEmail = config('shop.admin_email');

        if (! empty($adminEmail)) {
            $this->info("Sending drift report to {$adminEmail}...");

            try {
                Mail::to($adminEmail)->send(new InventoryReconciliationReport($mismatches));
            } catch (\Throwable $e) {
                Log::error('Failed to send inventory reconciliation email: '.$e->getMessage());
                $this->warn('Failed to dispatch email: '.$e->getMessage());
            }
        }

        return Command::FAILURE;
    }
}
