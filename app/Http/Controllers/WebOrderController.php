<?php

namespace App\Http\Controllers;

use App\Models\DistanceMatrix;
use App\Models\FeeSetting;
use App\Models\Order;
use App\Models\Trip;
use App\Services\ShippingCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebOrderController extends Controller
{
    private const COURIER_ACTIVE_STATUSES = ['COURIER_ASSIGNED', 'ACCEPTED', 'PICKED_UP', 'PURCHASED', 'OUT_FOR_DELIVERY', 'IN_TRANSIT', 'SHIPPED'];

    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role?->name;
        $courierBusy = $role === 'COURIER' && Order::where('courier_id', $user->id)->whereIn('status', self::COURIER_ACTIVE_STATUSES)->exists();
        $zones = DistanceMatrix::query()
            ->select('origin_zone')
            ->distinct()
            ->orderBy('origin_zone')
            ->pluck('origin_zone');

        $orders = Order::with(['customer', 'courier'])
            ->when($role === 'USER', fn ($query) => $query->where('customer_id', $user->id))
            ->when($role === 'COURIER', function ($query) use ($user, $request) {
                $zone = strtoupper((string) $request->query('zone', ''));
                $busy = Order::where('courier_id', $user->id)->whereIn('status', self::COURIER_ACTIVE_STATUSES)->exists();

                if ($busy) {
                    $query->where('courier_id', $user->id);
                    return;
                }

                $query->where(function ($available) use ($zone, $user) {
                    $available->where(function ($open) use ($zone) {
                        $open->where('status', 'SEARCHING_COURIER')
                            ->when($zone !== '', fn ($scoped) => $scoped->where('origin_zone', $zone));
                    })->orWhere('courier_id', $user->id);
                });
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->query('q');
                $query->where(function ($search) use ($term) {
                    $search->where('item_description', 'like', "%{$term}%")
                        ->orWhere('origin_zone', 'like', "%{$term}%")
                        ->orWhere('destination_zone', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($role === 'COURIER' && !$request->filled('status'), function ($query) use ($user) {
                $query->where(function ($scope) use ($user) {
                    $scope->where('status', 'SEARCHING_COURIER')->orWhere('courier_id', $user->id);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('orders.index', compact('orders', 'zones', 'role', 'courierBusy'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->role?->name === 'USER', 403);

        $zones = DistanceMatrix::query()
            ->select('origin_zone')
            ->distinct()
            ->orderBy('origin_zone')
            ->pluck('origin_zone');
        $routes = DistanceMatrix::orderBy('origin_zone')->orderBy('destination_zone')->get();
        $trip = $request->filled('trip_reference')
            ? Trip::where('code', $request->query('trip_reference'))->where('status', 'TRIP_OPEN')->with('traveler')->firstOrFail()
            : null;

        return view('orders.create', compact('zones', 'routes', 'trip'));
    }

    public function feed(Request $request)
    {
        abort_unless($request->user()->role?->name === 'COURIER', 403);

        $zone = strtoupper((string) $request->query('zone', ''));
        $courierBusy = Order::where('courier_id', $request->user()->id)->whereIn('status', self::COURIER_ACTIVE_STATUSES)->exists();

        if ($courierBusy) {
            return response()->json(['orders' => [], 'count' => 0, 'busy' => true, 'checked_at' => now()->toIso8601String()]);
        }

        $orders = Order::with('customer')
            ->where('status', 'SEARCHING_COURIER')
            ->when($zone !== '', fn ($query) => $query->where('origin_zone', $zone))
            ->latest()
            ->limit(30)
            ->get();

        return response()->json([
            'orders' => $orders->map(fn (Order $order) => [
                'id' => $order->id,
                'item' => $order->item_description,
                'origin' => $order->origin_zone,
                'destination' => $order->destination_zone,
                'customer' => $order->customer?->name ?? 'Pelanggan',
                'created_at' => $order->created_at?->toIso8601String(),
            ])->values(),
            'count' => $orders->count(),
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    public function store(Request $request, ShippingCalculator $shipping)
    {
        abort_unless($request->user()->role?->name === 'USER', 403);

        $data = $request->validate([
            'type' => 'nullable|in:ANTAR_WARGA,INTERNATIONAL_PO',
            'trip_reference' => 'required_if:type,INTERNATIONAL_PO|nullable|exists:trips,code',
            'origin_zone' => 'required_unless:type,INTERNATIONAL_PO|nullable|string|max:80|exists:distance_matrix,origin_zone',
            'destination_zone' => 'required_unless:type,INTERNATIONAL_PO|nullable|string|max:80|different:origin_zone',
            'pickup_address' => 'required|string|max:500',
            'delivery_address' => 'required|string|max:500',
            'items' => 'required|array|min:1|max:30',
            'items.*.name' => 'required|string|max:200',
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'items.*.notes' => 'nullable|string|max:500',
            'item_cost' => 'required|integer|min:0',
        ]);

        $type = $data['type'] ?? 'ANTAR_WARGA';
        if ($type === 'INTERNATIONAL_PO') {
            $trip = Trip::where('code', $data['trip_reference'])->where('status', 'TRIP_OPEN')->firstOrFail();
            $data['origin_zone'] = $trip->origin;
            $data['destination_zone'] = $trip->destination;
            $data['pickup_address'] = 'Dibeli traveler di '.$trip->origin;
        } else {
            $data['origin_zone'] = strtoupper($data['origin_zone']);
            $data['destination_zone'] = strtoupper($data['destination_zone']);
            $routeExists = DistanceMatrix::where('origin_zone', $data['origin_zone'])->where('destination_zone', $data['destination_zone'])->exists();
            abort_unless($routeExists, 422, 'Rute belum tersedia. Pilih kombinasi lokasi yang tersedia.');
        }

        $items = collect($data['items'])->values();
        $data['item_items'] = $items->all();
        $data['item_description'] = $items->map(fn ($item) => $item['name'])->implode(', ');
        $data['item_count'] = $items->sum('quantity');
        // Weight is no longer entered by the customer; use the minimum billable weight.
        $data['weight_lbs'] = 1;
        $quote = $type === 'INTERNATIONAL_PO'
            ? ['weight_lbs' => 1, 'distance_km' => 0, 'shipping_cost' => 0]
            : $shipping->quote($data['origin_zone'], $data['destination_zone'], $data['weight_lbs']);
        $data = array_merge($data, $quote, [
            'customer_id' => $request->user()->id,
            'type' => $type,
            'status' => $type === 'INTERNATIONAL_PO' ? 'PO_PENDING' : 'SEARCHING_COURIER',
            'commission' => 0,
            'courier_fee' => FeeSetting::value('courier_fee', 1000) * $data['item_count'],
        ]);

        $order = Order::create($data);

        return redirect()->route('orders.index')->with('success', "Permintaan berhasil dibuat. Kurir di {$order->origin_zone} dapat mengambilnya.");
    }

    public function claim(Request $request, Order $order)
    {
        abort_unless($request->user()->role?->name === 'COURIER', 403);

        $claimed = DB::transaction(function () use ($request, $order) {
            $alreadyWorking = Order::where('courier_id', $request->user()->id)
                ->whereIn('status', self::COURIER_ACTIVE_STATUSES)
                ->lockForUpdate()
                ->exists();
            if ($alreadyWorking) {
                return null;
            }

            $locked = Order::whereKey($order->id)->lockForUpdate()->first();
            if (!$locked || $locked->status !== 'SEARCHING_COURIER') {
                return null;
            }

            $locked->update(['courier_id' => $request->user()->id, 'status' => 'COURIER_ASSIGNED']);
            return $locked;
        });

        return $claimed
            ? redirect()->route('orders.index')->with('success', 'Pesanan berhasil diambil. Hubungi pelanggan dan beli barang sesuai rincian.')
            : back()->with('error', 'Kamu masih memiliki pesanan aktif. Selesaikan pesanan tersebut terlebih dahulu.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_unless($request->user()->role?->name === 'COURIER' && (int) $order->courier_id === (int) $request->user()->id, 403);

        $nextStatus = [
            'COURIER_ASSIGNED' => 'PICKED_UP',
            'ACCEPTED' => 'PICKED_UP',
            'PICKED_UP' => 'PURCHASED',
            'PURCHASED' => 'OUT_FOR_DELIVERY',
            'OUT_FOR_DELIVERY' => 'DELIVERED',
            'IN_TRANSIT' => 'DELIVERED',
        ][$order->status] ?? null;

        abort_unless($nextStatus, 422, 'Status pesanan ini sudah selesai atau tidak dapat dilanjutkan.');
        $order->update(['status' => $nextStatus]);

        return back()->with('success', 'Status diperbarui: '.str_replace('_', ' ', $nextStatus).'. Pelanggan dapat melihat pembaruannya.');
    }

    public function complete(Request $request, Order $order)
    {
        abort_unless($request->user()->role?->name === 'USER' && (int) $order->customer_id === (int) $request->user()->id, 403);
        abort_unless($order->status === 'DELIVERED', 422, 'Pesanan baru dapat diselesaikan setelah diantar.');

        $order->update(['status' => 'COMPLETED']);
        return back()->with('success', 'Pesanan dikonfirmasi selesai. Terima kasih.');
    }
}
