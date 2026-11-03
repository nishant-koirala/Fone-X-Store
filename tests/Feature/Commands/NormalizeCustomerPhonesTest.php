<?php

namespace Tests\Feature\Commands;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizeCustomerPhonesTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_is_dry_run_and_makes_no_changes(): void
    {
        $customer = Customer::factory()->create([
            'phone' => '+977-9812345678',
        ]);

        $this->artisan('customers:normalize-phones')
            ->expectsOutputToContain('DRY-RUN mode')
            ->expectsOutputToContain('Eligible records to normalize (dry-run)')
            ->assertSuccessful();

        $this->assertSame('+977-9812345678', $customer->fresh()->phone);
    }

    public function test_apply_normalizes_non_colliding_phones(): void
    {
        $customer = Customer::factory()->create([
            'phone' => '+977 9812345678',
        ]);

        $this->artisan('customers:normalize-phones', ['--apply' => true])
            ->expectsOutputToContain('APPLY mode')
            ->expectsOutputToContain('Successfully updated 1 customer record(s)')
            ->assertSuccessful();

        $this->assertSame('9812345678', $customer->fresh()->phone);
    }

    public function test_colliding_phones_are_detected_and_left_untouched(): void
    {
        // Customer 1 has clean '9812345678'
        $c1 = Customer::factory()->create([
            'phone' => '9812345678',
        ]);

        // Customer 2 has unnormalized '+977-9812345678' which would collide with c1
        $c2 = Customer::factory()->create([
            'phone' => '+977-9812345678',
        ]);

        $this->artisan('customers:normalize-phones', ['--apply' => true])
            ->expectsOutputToContain('Found phone number collisions (left untouched — customers are never merged)')
            ->assertSuccessful();

        // Neither record should be modified or merged
        $this->assertSame('9812345678', $c1->fresh()->phone);
        $this->assertSame('+977-9812345678', $c2->fresh()->phone);
        $this->assertCount(2, Customer::all());
    }

    public function test_already_clean_phones_are_left_unchanged(): void
    {
        Customer::factory()->create(['phone' => '9841234567']);

        $this->artisan('customers:normalize-phones', ['--apply' => true])
            ->expectsOutputToContain('Already Clean')
            ->assertSuccessful();
    }
}
