<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class WebTravelerController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role?->name === 'TRAVELER', 403);

        $trips = Trip::withCount('conversations')
            ->where('traveler_id', $request->user()->id)
            ->latest('departure_at')
            ->get();
        $tripCodes = $trips->pluck('code');
        $pendingOrders = Order::with('customer')
            ->where('type', 'INTERNATIONAL_PO')
            ->where('status', 'PO_PENDING')
            ->whereIn('trip_reference', $tripCodes)
            ->latest()
            ->get();
        $acceptedOrders = Order::with(['customer', 'traveler'])
            ->where('traveler_id', $request->user()->id)
            ->whereIn('status', ['PO_ACCEPTED', 'PURCHASED', 'SHIPPED', 'DELIVERED'])
            ->latest()
            ->get();

        return view('traveler.dashboard', compact('trips', 'pendingOrders', 'acceptedOrders'));
    }

    public function storeTrip(Request $request)
    {
        abort_unless($request->user()->role?->name === 'TRAVELER', 403);

        $data = $request->validate([
            'origin' => 'required|string|max:120',
            'destination' => 'required|string|max:120',
            'departure_at' => 'required|date|after:now',
            'arrival_at' => 'nullable|date|after:departure_at',
            'dp_required' => 'required|boolean',
            'dp_percent' => 'required|integer|min:0|max:100',
            'product_details' => 'nullable|string|max:2000',
            'product_image' => 'nullable|image|max:5120',
            'products' => 'required|array|min:1|max:30',
            'products.*.name' => 'required|string|max:120',
            'products.*.description' => 'nullable|string|max:500',
            'products.*.price' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('product_image')) {
            $data['product_image'] = Storage::disk('public')->url($request->file('product_image')->store('trip-products', 'public'));
        }
        $data['product_items'] = $data['products'];
        unset($data['products']);
        $trip = Trip::create(array_merge($data, [
            'traveler_id' => $request->user()->id,
            'code' => 'TRIP-'.Str::upper(Str::random(8)),
            'status' => 'TRIP_OPEN',
        ]));

        return back()->with('success', "Perjalanan {$trip->code} berhasil dipublikasikan.");
    }

    public function acceptOrder(Request $request, Order $order)
    {
        abort_unless($request->user()->role?->name === 'TRAVELER', 403);
        abort_unless($order->type === 'INTERNATIONAL_PO' && $order->status === 'PO_PENDING', 422, 'Pesanan ini sudah diproses traveler lain.');

        $ownsTrip = Trip::where('traveler_id', $request->user()->id)->where('code', $order->trip_reference)->exists();
        abort_unless($ownsTrip, 403, 'Pesanan ini bukan untuk perjalananmu.');

        $order->update(['traveler_id' => $request->user()->id, 'status' => 'PO_ACCEPTED']);
        return back()->with('success', 'Pesanan diterima. Hubungi pelanggan melalui chat dan proses barangnya.');
    }

    public function feed(Request $request)
    {
        abort_unless($request->user()->role?->name === 'TRAVELER', 403);
        $codes = Trip::where('traveler_id', $request->user()->id)->pluck('code');
        $orders = Order::where('type', 'INTERNATIONAL_PO')->where('status', 'PO_PENDING')->whereIn('trip_reference', $codes)->latest()->get(['id', 'trip_reference', 'item_description', 'created_at']);
        return response()->json(['count' => $orders->count(), 'orders' => $orders]);
    }
}
