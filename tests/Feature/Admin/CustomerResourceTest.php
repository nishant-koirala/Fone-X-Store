<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    public function test_can_create_customer_and_normalizes_phone(): void
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => 'Ram Bahadur Shrestha',
                'phone' => '+977-9841234567',
                'email' => 'ram@example.com',
                'address' => 'Kathmandu, Baneshwor',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $customer = Customer::where('name', 'Ram Bahadur Shrestha')->first();
        $this->assertNotNull($customer);
        // Normalized to standard 10-digit Nepali format
        $this->assertSame('9841234567', $customer->phone);
    }

    public function test_rejects_invalid_nepali_phone(): void
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => 'Invalid Phone Customer',
                'phone' => '1234567890',
                'email' => 'invalid@example.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);
    }

    public function test_rejects_duplicate_colliding_phone(): void
    {
        Customer::factory()->create(['phone' => '9841234567']);

        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => 'Duplicate Customer',
                'phone' => '+977 9841234567',
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);
    }
}
