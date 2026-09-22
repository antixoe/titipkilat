<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripConversation;
use Illuminate\Http\Request;

class WebTripChatController extends Controller
{
    public function show(Request $request, Trip $trip)
    {
        abort_unless($trip->status === 'TRIP_OPEN', 404);

        if ($request->user()->id === $trip->traveler_id) {
            $conversations = TripConversation::with(['customer', 'messages.sender'])
                ->where('trip_id', $trip->id)
                ->latest('updated_at')
                ->get();

            return response()->json(['conversations' => $conversations]);
        }

        $conversation = $this->conversationFor($request, $trip);
        return response()->json(['conversation' => $conversation?->load('messages.sender')]);
    }

    public function store(Request $request, Trip $trip)
    {
        $conversation = $this->conversationFor($request, $trip, true);
        $data = $request->validate(['body' => 'required|string|max:2000']);
        $message = $conversation->messages()->create(['sender_id' => $request->user()->id, 'body' => $data['body']]);
        $conversation->touch();

        return response()->json(['message' => $message->load('sender')], 201);
    }

    private function conversationFor(Request $request, Trip $trip, bool $create = false): ?TripConversation
    {
        abort_unless($trip->status === 'TRIP_OPEN', 404);
        $user = $request->user();

        if ((int) $user->id === (int) $trip->traveler_id) {
            $conversation = TripConversation::whereKey($request->query('conversation_id', $request->input('conversation_id')))
                ->where('trip_id', $trip->id)
                ->first();
            abort_unless($conversation, 422, 'Pilih percakapan pelanggan terlebih dahulu.');
            return $conversation;
        }

        abort_unless($user->role?->name === 'USER', 403);
        return $create
            ? TripConversation::firstOrCreate(['trip_id' => $trip->id, 'customer_id' => $user->id])
            : TripConversation::where('trip_id', $trip->id)->where('customer_id', $user->id)->first();
    }
}
