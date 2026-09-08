<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, string $role)
    {
        return response()->json([
            'role' => $role,
            'user' => $request->user()->load('role', 'wallet'),
            'capabilities' => match ($role) {
                'USER' => ['create_order', 'top_up_wallet', 'review_courier'],
                'COURIER' => ['claim_order', 'upload_receipt', 'update_delivery'],
                'TRAVELER' => ['create_trip', 'manage_pre_orders', 'upload_item_proof'],
                'ADMIN' => ['manage_users', 'manage_orders', 'view_reports'],
                'OPERATOR' => ['verify_payments', 'monitor_orders', 'resolve_support'],
                'SUPER_ADMIN' => ['audit_ledger', 'configure_fees', 'monitor_system'],
            },
        ]);
    }
}
