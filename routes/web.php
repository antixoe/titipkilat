<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebAuthController;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Trip;
use App\Models\EWallet;
use App\Models\User;
use App\Models\Dispute;
use App\Models\WalletTransaction;
Route::get('/', function () {
    return Auth::check() && Auth::user()->role?->name === 'SUPER_ADMIN'
        ? redirect('/dashboard/super-admin')
        : view('home');
});
Route::get('/login', [WebAuthController::class, 'login'])->name('login');
Route::post('/login', [WebAuthController::class, 'authenticate'])->name('login.authenticate');
Route::get('/signup', [WebAuthController::class, 'signup'])->name('signup');
Route::post('/signup', [WebAuthController::class, 'register'])->name('signup.register');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::post('/settings/account/soft-delete', [WebAuthController::class, 'softDeleteAccount'])->middleware('auth')->name('settings.account.soft-delete');
Route::post('/settings/account/hard-delete', [WebAuthController::class, 'hardDeleteAccount'])->middleware('auth')->name('settings.account.hard-delete');
Route::get('/orders', function (\Illuminate\Http\Request $request) { $sorts=['latest'=>'created_at','oldest'=>'created_at','cost_high'=>'shipping_cost','cost_low'=>'shipping_cost']; $sort=$request->query('sort','latest'); $query=Order::with(['customer','courier'])->when($request->filled('q'),fn($q)=>$q->where(fn($search)=>$search->where('item_description','like','%'.$request->q.'%')->orWhere('origin_zone','like','%'.$request->q.'%')->orWhere('destination_zone','like','%'.$request->q.'%')->orWhereHas('customer',fn($user)=>$user->where('name','like','%'.$request->q.'%'))))->when($request->filled('status'),fn($q)=>$q->where('status',$request->status))->when($request->filled('type'),fn($q)=>$q->where('type',$request->type))->orderBy($sorts[$sort] ?? 'created_at', in_array($sort,['cost_high','latest'],true) ? 'desc' : 'asc'); return view('orders.index', ['orders'=>$query->paginate(10)->withQueryString()]); });
Route::get('/orders/create', function () { if (Auth::check() && Auth::user()->role?->name === 'SUPER_ADMIN') return redirect('/orders')->with('error', 'Super Admin hanya dapat melihat pesanan.'); return view('orders.create'); });
Route::view('/wallet', 'wallet');
Route::view('/wallet/top-up', 'wallet');
Route::get('/trips', function (\Illuminate\Http\Request $request) { $sort=$request->query('sort','soonest'); $sortColumn=$sort==='destination'?'destination':'departure_at'; $direction=$sort==='latest'?'desc':'asc'; $trips=Trip::with('traveler')->where('status','TRIP_OPEN')->when($request->filled('q'),fn($query)=>$query->where(fn($q)=>$q->where('code','like','%'.$request->q.'%')->orWhere('origin','like','%'.$request->q.'%')->orWhere('destination','like','%'.$request->q.'%')))->when($request->filled('destination'),fn($query)=>$query->where('destination',$request->destination))->when($request->filled('dp'),fn($query)=>$query->where('dp_percent',$request->dp))->orderBy($sortColumn,$direction)->paginate(10)->withQueryString(); return view('trips', ['trips'=>$trips]); });
Route::get('/settings', function (\Illuminate\Http\Request $request) { $isSuperAdmin=Auth::check() && Auth::user()->role?->name === 'SUPER_ADMIN'; $activityQuery=ActivityLog::with('user')->when($request->filled('q'),fn($q)=>$q->where(fn($search)=>$search->where('description','like','%'.$request->q.'%')->orWhere('action','like','%'.$request->q.'%')->orWhere('ip_address','like','%'.$request->q.'%')->orWhere('route_name','like','%'.$request->q.'%')->orWhereHas('user',fn($user)=>$user->where('name','like','%'.$request->q.'%'))))->when($request->filled('action'),fn($q)=>$q->where('action',$request->action))->when($request->filled('entity_type'),fn($q)=>$q->where('entity_type',$request->entity_type)); $sort=$request->query('sort','latest'); $activityLogs=$isSuperAdmin ? $activityQuery->orderBy($sort==='action'?'action':'created_at',$sort==='action'?'asc':($sort==='oldest'?'asc':'desc'))->paginate(10)->withQueryString() : collect(); return view('settings', ['canViewActivityLogs'=>$isSuperAdmin,'activityLogs'=>$activityLogs,'activityActions'=>$isSuperAdmin ? ActivityLog::distinct()->orderBy('action')->pluck('action') : collect(),'activityEntities'=>$isSuperAdmin ? ActivityLog::whereNotNull('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type') : collect()]); });
Route::resource('users', UserController::class)->except(['show']);
Route::patch('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password.reset');
Route::post('/roles', [UserController::class, 'storeRole'])->name('roles.store');
Route::put('/roles/{role}', [UserController::class, 'updateRole'])->name('roles.update');
Route::delete('/roles/{role}', [UserController::class, 'destroyRole'])->name('roles.destroy');
Route::get('/dashboard/{role}', function (string $role) {
    abort_unless(in_array($role, ['user','courier','traveler','admin','operator','super-admin'], true), 404);

    if ($role === 'super-admin') {
        abort_unless(Auth::check() && Auth::user()->role?->name === 'SUPER_ADMIN', 403);

        $ordersTrend = collect(range(5, 0))->map(function (int $monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            return [
                'label' => $date->format('M'),
                'value' => Order::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        });

        return view('super_admin_dashboard', [
            'title' => 'Dashboard Super Admin',
            'ordersTrend' => $ordersTrend,
            'stats' => [
                'users' => User::count(),
                'pendingKyc' => User::where('kyc_status', 'PENDING')->count(),
                'walletBalance' => EWallet::sum('balance'),
                'activeOrders' => Order::where('status', '!=', 'COMPLETED')->count(),
                'openDisputes' => Dispute::where('status', 'OPEN')->count(),
            ],
            'recentTransactions' => WalletTransaction::with('wallet.user')->latest()->paginate(6)->withQueryString(),
        ]);
    }

    return view('dashboard', ['role' => strtoupper(str_replace('-', ' ', $role))]);
})->middleware('auth');
