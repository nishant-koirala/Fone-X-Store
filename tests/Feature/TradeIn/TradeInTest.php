<?php

namespace Tests\Feature\TradeIn;

use App\Mail\AdminTradeInAlert;
use App\Models\Customer;
use App\Models\TradeInValuation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TradeInTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_trade_in_form(): void
    {
        $response = $this->get(route('trade-in.create'));

        $response->assertStatus(200);
        $response->assertViewIs('trade-in.create');
        $response->assertSee('Trade & Upgrade', false);
        $response->assertSee('INSTANT VALUATION');
    }

    public function test_form_validation_for_required_fields(): void
    {
        $response = $this->post(route('trade-in.store'), []);

        $response->assertSessionHasErrors([
            'device_brand',
            'device_model',
            'condition',
            'customer_name',
            'customer_phone',
        ]);
    }

    public function test_tampered_or_invalid_condition_value_is_rejected(): void
    {
        $response = $this->post(route('trade-in.store'), [
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13',
            'condition' => 'tampered_condition_string', // only flawless, good, broken allowed
            'customer_name' => 'Kiran Poudel',
            'customer_phone' => '9841223344',
        ]);

        $response->assertSessionHasErrors(['condition']);
        $this->assertDatabaseCount('trade_in_valuations', 0);
    }

    public function test_can_successfully_submit_trade_in_request(): void
    {
        Mail::fake();

        $response = $this->post(route('trade-in.store'), [
            'device_brand' => 'Samsung',
            'device_model' => 'Galaxy S22 Ultra',
            'condition' => 'good',
            'customer_name' => 'Bibek Khadka',
            'customer_phone' => '9801234567',
            'additional_notes' => 'Minor scratch on the bottom edge, 92% battery',
        ]);

        // Customer created
        $customer = Customer::where('phone', '9801234567')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Bibek Khadka', $customer->name);

        // TradeInValuation created with pending status
        $valuation = TradeInValuation::where('customer_id', $customer->id)->first();
        $this->assertNotNull($valuation);
        $this->assertEquals('Samsung', $valuation->device_brand);
        $this->assertEquals('Galaxy S22 Ultra', $valuation->device_model);
        $this->assertEquals('pending', $valuation->status);
        $this->assertStringContainsString('Overall Condition: Good', $valuation->condition_description);
        $this->assertStringContainsString('Minor scratch', $valuation->condition_description);
        $this->assertNull($valuation->estimated_value);

        // Redirects to confirmation
        $response->assertRedirect(route('trade-in.confirmation', $valuation));

        // Mail alerts sent
        Mail::assertSent(AdminTradeInAlert::class);

        // Session key set
        $this->assertEquals($valuation->id, session('last_valuation_id'));
    }

    public function test_trade_in_confirmation_page_idor_protection(): void
    {
        $valuation = TradeInValuation::factory()->create();

        // Without matching session key, redirected to home with error
        $responseUnauthorized = $this->get(route('trade-in.confirmation', $valuation));
        $responseUnauthorized->assertRedirect(route('home'));
        $responseUnauthorized->assertSessionHas('error');

        // With spoofed/different valuation id, also redirected
        $responseSpoofed = $this->withSession(['last_valuation_id' => $valuation->id + 100])
            ->get(route('trade-in.confirmation', $valuation));
        $responseSpoofed->assertRedirect(route('home'));

        // With legitimate matching session id, 200 OK
        $responseAuthorized = $this->withSession(['last_valuation_id' => $valuation->id])
            ->get(route('trade-in.confirmation', $valuation));
        $responseAuthorized->assertStatus(200);
        $responseAuthorized->assertViewIs('trade-in.confirmation');
        $responseAuthorized->assertSee($valuation->device_brand);
        $responseAuthorized->assertSee($valuation->device_model);
    }
}
