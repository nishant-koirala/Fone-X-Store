<?php

namespace Tests\Feature\Commands;

use App\Enums\DeviceUnitStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Mail\InventoryReconciliationReport;
use App\Models\DeviceUnit;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Rules\Imei;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReconcileInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_inventory_exits_successfully_with_zero_code(): void
    {
        // 1. Clean untracked condition: 5 stock, +5 stock movement
        $product = Product::factory()->create();
        $untracked = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'tracks_units' => false,
            'quantity_in_stock' => 5,
        ]);
        $untracked->stockMovements()->create([
            'type' => StockMovementTypeEnum::RESTOCK,
            'quantity' => 5,
            'note' => 'Initial batch',
        ]);

        // 2. Clean tracked condition: 2 stock, 2 available units, +2 stock movement
        $tracked = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'tracks_units' => true,
            'quantity_in_stock' => 2,
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $tracked->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'imei' => Imei::fake(),
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $tracked->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'imei' => Imei::fake(),
        ]);
        $tracked->stockMovements()->create([
            'type' => StockMovementTypeEnum::RESTOCK,
            'quantity' => 2,
            'note' => 'Initial tracked batch',
        ]);

        $this->artisan('inventory:reconcile')
            ->expectsOutputToContain('Inventory audit clean')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_detects_tracked_condition_units_discrepancy(): void
    {
        $product = Product::factory()->create();
        $tracked = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'tracks_units' => true,
            'quantity_in_stock' => 2,
        ]);
        // Only 1 available unit registered for 2 in stock
        DeviceUnit::factory()->create([
            'product_condition_id' => $tracked->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'imei' => Imei::fake(),
        ]);
        $tracked->stockMovements()->create([
            'type' => StockMovementTypeEnum::RESTOCK,
            'quantity' => 2,
        ]);

        $this->artisan('inventory:reconcile')
            ->expectsOutputToContain('Inventory drift detected!')
            ->expectsOutputToContain('Stock (2) != Units (1)')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_detects_tracked_condition_movement_sum_discrepancy(): void
    {
        $product = Product::factory()->create();
        $tracked = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'tracks_units' => true,
            'quantity_in_stock' => 2,
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $tracked->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'imei' => Imei::fake(),
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $tracked->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'imei' => Imei::fake(),
        ]);
        // Movement sum is 3 while stock is 2
        $tracked->stockMovements()->create([
            'type' => StockMovementTypeEnum::RESTOCK,
            'quantity' => 3,
        ]);

        $this->artisan('inventory:reconcile')
            ->expectsOutputToContain('Inventory drift detected!')
            ->expectsOutputToContain('Stock (2) != Movements (3)')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_detects_untracked_condition_movement_sum_discrepancy(): void
    {
        $product = Product::factory()->create();
        $untracked = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'tracks_units' => false,
            'quantity_in_stock' => 10,
        ]);
        // Movements total 7 instead of 10
        $untracked->stockMovements()->create([
            'type' => StockMovementTypeEnum::RESTOCK,
            'quantity' => 7,
        ]);

        $this->artisan('inventory:reconcile')
            ->expectsOutputToContain('Inventory drift detected!')
            ->expectsOutputToContain('Stock (10) != Movements (7)')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_emails_report_when_admin_email_configured_and_mismatches_exist(): void
    {
        Mail::fake();
        config(['shop.admin_email' => 'alerts@fonex.com.np']);

        $product = Product::factory()->create();
        $untracked = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'tracks_units' => false,
            'quantity_in_stock' => 5,
        ]);
        $untracked->stockMovements()->create([
            'type' => StockMovementTypeEnum::RESTOCK,
            'quantity' => 4, // Discrepancy
        ]);

        $this->artisan('inventory:reconcile')
            ->assertExitCode(Command::FAILURE);

        Mail::assertSent(InventoryReconciliationReport::class, function ($mail) {
            return $mail->hasTo('alerts@fonex.com.np')
                && count($mail->mismatches) === 1;
        });
    }

    public function test_logs_warning_and_does_not_email_when_admin_email_empty(): void
    {
        Mail::fake();
        Log::spy();
        config(['shop.admin_email' => null]);
        putenv('ADMIN_EMAIL=');

        $product = Product::factory()->create();
        $untracked = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'tracks_units' => false,
            'quantity_in_stock' => 5,
        ]);
        $untracked->stockMovements()->create([
            'type' => StockMovementTypeEnum::RESTOCK,
            'quantity' => 2,
        ]);

        $this->artisan('inventory:reconcile')
            ->assertExitCode(Command::FAILURE);

        Mail::assertNothingSent();
        Log::shouldHaveReceived('warning');
    }

    public function test_never_modifies_data_during_reconciliation(): void
    {
        $product = Product::factory()->create();
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'tracks_units' => true,
            'quantity_in_stock' => 3,
        ]);
        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'imei' => Imei::fake(),
        ]);
        $condition->stockMovements()->create([
            'type' => StockMovementTypeEnum::RESTOCK,
            'quantity' => 2,
        ]);

        $this->artisan('inventory:reconcile')
            ->assertExitCode(Command::FAILURE);

        $this->assertEquals(3, $condition->fresh()->quantity_in_stock);
        $this->assertEquals(1, $condition->availableDeviceUnits()->count());
        $this->assertEquals(2, $condition->stockMovements()->sum('quantity'));
    }
}
