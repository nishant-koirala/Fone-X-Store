<?php

namespace Tests\Feature;

use App\Mail\AdminTradeInAlert;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TradeInTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_submit_trade_in_request()
    {
        Mail::fake();

        $response = $this->post(route('trade-in.store'), [
            'customer_name' => 'Jane Doe',
            'customer_phone' => '9812345678',
            'email' => 'jane@example.com',
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'condition' => 'good',
            'additional_notes' => 'minor scratches',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'name' => 'Jane Doe',
            'phone' => '9812345678',
        ]);

        $this->assertDatabaseHas('trade_in_valuations', [
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'status' => 'pending',
        ]);

        Mail::assertQueued(AdminTradeInAlert::class);
    }

    public function test_trade_in_does_not_set_placeholder_address_and_does_not_overwrite_existing_name(): void
    {
        Mail::fake();

        // Pre-existing customer
        $customer = Customer::factory()->create([
            'name' => 'Original Customer Name',
            'phone' => '9841112233',
            'email' => 'original@example.com',
            'address' => 'Jawalakhel, Lalitpur',
        ]);

        $response = $this->post(route('trade-in.store'), [
            'customer_name' => 'Attempted Overwrite Name',
            'customer_phone' => '9841112233',
            'device_brand' => 'Samsung',
            'device_model' => 'Galaxy S23',
            'condition' => 'good',
        ]);

        $response->assertRedirect();

        $fresh = $customer->fresh();
        $this->assertEquals('Original Customer Name', $fresh->name);
        $this->assertEquals('Jawalakhel, Lalitpur', $fresh->address);
    }

    public function test_new_trade_in_customer_gets_null_address_not_placeholder_string(): void
    {
        Mail::fake();

        $response = $this->post(route('trade-in.store'), [
            'customer_name' => 'Fresh User',
            'customer_phone' => '9849998877',
            'device_brand' => 'Google',
            'device_model' => 'Pixel 8',
            'condition' => 'flawless',
        ]);

        $response->assertRedirect();

        $customer = Customer::where('phone', '9849998877')->first();
        $this->assertNotNull($customer);
        $this->assertNull($customer->address);
        $this->assertNotEquals('Trade-In Request Submission', $customer->address);
    }
}
