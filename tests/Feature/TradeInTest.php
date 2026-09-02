<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\TradeInValuation;
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
            'phone' => '9812345678',
            'email' => 'jane@example.com',
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'condition_screen' => 'perfect',
            'condition_body' => 'minor_scratches',
            'condition_functional' => 'yes',
            'accessories' => ['box', 'charger']
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('customers', [
            'name' => 'Jane Doe',
            'phone' => '9812345678'
        ]);

        $this->assertDatabaseHas('trade_in_valuations', [
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'status' => 'pending'
        ]);

        Mail::assertSent(\App\Mail\TradeInSubmitted::class);
        Mail::assertSent(\App\Mail\AdminTradeInAlert::class);
    }
}
