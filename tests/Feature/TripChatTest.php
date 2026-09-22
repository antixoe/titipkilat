<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_and_traveler_can_chat_about_a_trip(): void
    {
        $traveler = $this->userWithRole('TRAVELER');
        $customer = $this->userWithRole('USER');
        $trip = Trip::create([
            'traveler_id' => $traveler->id,
            'code' => 'TRIP-CHAT001',
            'origin' => 'Jakarta',
            'destination' => 'Tokyo',
            'departure_at' => now()->addDays(4),
            'status' => 'TRIP_OPEN',
        ]);

        $this->actingAs($customer)->get('/trips')->assertOk();

        $response = $this->actingAs($customer)->post(route('trips.chat.store', $trip), ['body' => 'Bisa titip sunscreen SPF50?']);
        $response->assertCreated();
        $conversationId = $response->json('message.conversation_id');

        $this->actingAs($traveler)
            ->get(route('trips.chat.show', [$trip, 'conversation_id' => $conversationId]))
            ->assertOk()
            ->assertJsonPath('conversations.0.messages.0.body', 'Bisa titip sunscreen SPF50?');

        $this->actingAs($traveler)
            ->post(route('trips.chat.store', $trip), ['conversation_id' => $conversationId, 'body' => 'Bisa, kirim detail produknya ya.'])
            ->assertCreated();
    }

    public function test_traveler_can_open_their_workspace(): void
    {
        $traveler = $this->userWithRole('TRAVELER');

        $this->actingAs($traveler)
            ->get('/traveler')
            ->assertOk();
    }

    private function userWithRole(string $name): User
    {
        return User::factory()->create(['role_id' => Role::firstOrCreate(['name' => $name])->id]);
    }
}
