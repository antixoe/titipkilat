<?php

namespace Tests\Feature;

use App\Models\DistanceMatrix;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_publish_a_location_based_request(): void
    {
        DistanceMatrix::create(['origin_zone' => 'JAKARTA', 'destination_zone' => 'BANDUNG', 'distance_km' => 150]);
        $customer = $this->userWithRole('USER');
        $this->actingAs($customer)->get('/orders')->assertOk();

        $response = $this->actingAs($customer)->post('/orders', [
            'origin_zone' => 'JAKARTA',
            'destination_zone' => 'BANDUNG',
            'pickup_address' => 'Toko A, Jakarta',
            'delivery_address' => 'Jl. B, Bandung',
            'items' => [['name' => 'Sunscreen SPF50', 'quantity' => 1, 'notes' => '60ml']],
            'item_cost' => 150000,
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'origin_zone' => 'JAKARTA',
            'destination_zone' => 'BANDUNG',
            'status' => 'SEARCHING_COURIER',
        ]);
    }

    public function test_courier_can_only_claim_an_open_request_once(): void
    {
        $customer = $this->userWithRole('USER');
        $courier = $this->userWithRole('COURIER');
        $otherCourier = $this->userWithRole('COURIER');
        $order = Order::create([
            'customer_id' => $customer->id,
            'type' => 'ANTAR_WARGA',
            'status' => 'SEARCHING_COURIER',
            'origin_zone' => 'JAKARTA',
            'destination_zone' => 'BANDUNG',
            'pickup_address' => 'Toko A',
            'delivery_address' => 'Rumah B',
            'item_description' => 'Barang titipan',
            'item_count' => 1,
            'weight_lbs' => 1,
        ]);

        $this->actingAs($courier)->post(route('orders.claim', $order))->assertRedirect();
        $this->actingAs($courier)->get(route('orders.feed'))->assertOk()->assertJsonPath('busy', true)->assertJsonCount(0, 'orders');
        $this->actingAs($otherCourier)->post(route('orders.claim', $order))->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'courier_id' => $courier->id, 'status' => 'COURIER_ASSIGNED']);
    }

    private function userWithRole(string $name): User
    {
        return User::factory()->create(['role_id' => Role::firstOrCreate(['name' => $name])->id]);
    }
}
