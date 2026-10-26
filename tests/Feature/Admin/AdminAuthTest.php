<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_access_admin_dashboard(): void
    {
        $admin = $this->loginAsAdmin();

        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('FoneX Store Admin');
    }

    public function test_non_admin_user_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create(['email' => 'regularcustomer@gmail.com']);
        $this->actingAs($customer);

        $response = $this->get('/admin');

        $response->assertStatus(403);
    }
}
