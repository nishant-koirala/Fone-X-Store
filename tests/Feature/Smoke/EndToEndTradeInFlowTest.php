<?php

namespace Tests\Feature\Smoke;

use App\Filament\Resources\TradeInValuationResource\Pages\EditTradeInValuation;
use App\Filament\Resources\TradeInValuationResource\Pages\ListTradeInValuations;
use App\Mail\AdminTradeInAlert;
use App\Models\Customer;
use App\Models\TradeInValuation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class EndToEndTradeInFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_end_to_end_trade_in_journey(): void
    {
        Mail::fake();

        // 1. Step: Customer visits trade-in valuation page
        $formPageResponse = $this->get(route('trade-in.create'));
        $formPageResponse->assertOk();
        $formPageResponse->assertSee('Trade-In');

        // 2. Step: Customer submits device valuation request
        $tradeInPayload = [
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13 Pro',
            'condition' => 'good',
            'customer_name' => 'Bikash Thapa',
            'customer_phone' => '9801234567',
            'additional_notes' => 'Minor scratch on bezel, battery health 88%',
        ];

        $postResponse = $this->post(route('trade-in.store'), $tradeInPayload);
        $postResponse->assertSessionHasNoErrors();

        $valuation = TradeInValuation::latest()->first();
        $this->assertNotNull($valuation, 'Valuation record should be created');
        $postResponse->assertRedirect(route('trade-in.confirmation', $valuation));

        // 3. Step: Database assertions
        $this->assertDatabaseHas('customers', [
            'name' => 'Bikash Thapa',
            'phone' => '9801234567',
        ]);

        $this->assertDatabaseHas('trade_in_valuations', [
            'id' => $valuation->id,
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 13 Pro',
            'status' => 'pending',
            'estimated_value' => null,
        ]);

        // 4. Step: Notification alerts
        Mail::assertSent(AdminTradeInAlert::class, function (AdminTradeInAlert $mail) use ($valuation) {
            return $mail->valuation->id === $valuation->id;
        });

        // 5. Step: Customer views confirmation page
        $confirmationResponse = $this->get(route('trade-in.confirmation', $valuation));
        $confirmationResponse->assertOk();
        $confirmationResponse->assertSee('Bikash Thapa');
        $confirmationResponse->assertSee('iPhone 13 Pro');

        // 6. Step: Admin Staff reviews and prices the valuation in Filament
        $admin = User::factory()->create([
            'email' => 'admin@fonexstore.test',
        ]);
        $this->actingAs($admin);

        // Admin sees the pending valuation in the Filament table
        Livewire::test(ListTradeInValuations::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$valuation]);

        // Admin reviews the device and sets the estimated offer value
        Livewire::test(EditTradeInValuation::class, ['record' => $valuation->getRouteKey()])
            ->fillForm([
                'estimated_value' => 68000,
                'status' => 'reviewed',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // 7. Step: Verify final database status
        $fresh = $valuation->fresh();
        $this->assertEquals('reviewed', $fresh->status);
        $this->assertEquals(68000, (float) $fresh->estimated_value);
    }
}
