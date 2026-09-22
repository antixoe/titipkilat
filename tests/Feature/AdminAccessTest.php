<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_open_user_management_crud(): void
    {
        $customer = User::factory()->create([
            'role_id' => Role::create(['name' => 'USER'])->id,
        ]);

        $this->actingAs($customer)
            ->get('/users')
            ->assertForbidden();
    }

    public function test_admin_can_open_user_management_crud(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::create(['name' => 'ADMIN'])->id,
        ]);

        $this->actingAs($admin)
            ->get('/users')
            ->assertOk();
    }

    public function test_customer_cannot_open_another_role_dashboard(): void
    {
        $customer = User::factory()->create([
            'role_id' => Role::create(['name' => 'USER'])->id,
        ]);

        $this->actingAs($customer)
            ->get('/dashboard/admin')
            ->assertForbidden();
    }

    public function test_courier_cannot_open_traveler_marketplace(): void
    {
        $courier = User::factory()->create([
            'role_id' => Role::create(['name' => 'COURIER'])->id,
        ]);

        $this->actingAs($courier)
            ->get('/trips')
            ->assertForbidden();
    }

    public function test_traveler_cannot_open_customer_order_or_marketplace_pages(): void
    {
        $traveler = User::factory()->create([
            'role_id' => Role::create(['name' => 'TRAVELER'])->id,
        ]);

        $this->actingAs($traveler)
            ->get('/orders')
            ->assertForbidden();

        $this->actingAs($traveler)
            ->get('/trips')
            ->assertForbidden();
    }
}
