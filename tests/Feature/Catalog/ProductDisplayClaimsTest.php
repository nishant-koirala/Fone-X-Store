<?php

namespace Tests\Feature\Catalog;

use App\Enums\DeviceUnitStatusEnum;
use App\Models\DeviceUnit;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Rules\Imei;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDisplayClaimsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracked_condition_displays_battery_range_when_units_have_battery_health(): void
    {
        $product = Product::factory()->create(['name' => 'iPhone 15 Pro Tracked', 'is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'A',
            'price' => 120000,
            'quantity_in_stock' => 2,
            'tracks_units' => true,
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'battery_health' => 86,
            'imei' => Imei::fake(),
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'battery_health' => 94,
            'imei' => Imei::fake(),
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Battery: 86% - 94%');
        $response->assertSee('IMEI registered');
    }

    public function test_tracked_condition_displays_single_battery_health_when_all_units_identical(): void
    {
        $product = Product::factory()->create(['name' => 'iPhone 13 Single Battery', 'is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'A',
            'price' => 75000,
            'quantity_in_stock' => 1,
            'tracks_units' => true,
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'battery_health' => 89,
            'imei' => Imei::fake(),
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Battery: 89%');
        $response->assertSee('IMEI registered');
    }

    public function test_tracked_condition_omits_imei_claim_if_any_available_unit_missing_imei(): void
    {
        $product = Product::factory()->create(['name' => 'iPhone 14 Partial IMEI', 'is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'B',
            'price' => 85000,
            'quantity_in_stock' => 2,
            'tracks_units' => true,
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'battery_health' => 88,
            'imei' => Imei::fake(),
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'battery_health' => 90,
            'imei' => null,
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Battery: 88% - 90%');
        $response->assertDontSee('IMEI registered');
    }

    public function test_tracked_condition_omits_battery_claim_if_no_units_have_battery_health(): void
    {
        $product = Product::factory()->create(['name' => 'iPhone 12 No Battery Data', 'is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'B',
            'price' => 55000,
            'quantity_in_stock' => 2,
            'tracks_units' => true,
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'battery_health' => null,
            'imei' => Imei::fake(),
        ]);

        DeviceUnit::factory()->create([
            'product_condition_id' => $condition->id,
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'battery_health' => null,
            'imei' => Imei::fake(),
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertDontSee('Battery:');
        $response->assertSee('IMEI registered');
    }

    public function test_untracked_condition_displays_generic_wording_and_no_unit_claims(): void
    {
        $product = Product::factory()->create(['name' => 'Pixel 8 Untracked', 'is_visible' => true]);
        ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'A',
            'price' => 90000,
            'quantity_in_stock' => 5,
            'tracks_units' => false,
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Near-flawless display & body • Strong battery health • Thoroughly inspected', false);
        $response->assertDontSee('IMEI registered');
        $response->assertDontSee('Battery:');
    }
}
