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
use App\Http\Controllers\WebWalletController;
use App\Http\Controllers\WebOrderController;
use App\Http\Controllers\WebTripChatController;
use App\Http\Controllers\WebTravelerController;
Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role?->name;
        if ($role === 'SUPER_ADMIN') return redirect('/dashboard/super-admin');
        if ($role === 'ADMIN') return redirect('/dashboard/admin');
        if ($role === 'TRAVELER') return redirect('/traveler');
    }

    return view('home');
});
Route::get('/login', [WebAuthController::class, 'login'])->name('login');
Route::post('/login', [WebAuthController::class, 'authenticate'])->name('login.authenticate');
Route::get('/signup', [WebAuthController::class, 'signup'])->name('signup');
Route::post('/signup', [WebAuthController::class, 'register'])->name('signup.register');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::get('/password/change', [WebAuthController::class, 'changePassword'])->middleware('auth')->name('password.change');
Route::post('/password/change', [WebAuthController::class, 'updatePassword'])->middleware('auth')->name('password.change.update');
Route::post('/settings/account/soft-delete', [WebAuthController::class, 'softDeleteAccount'])->middleware('auth')->name('settings.account.soft-delete');
Route::post('/settings/account/hard-delete', [WebAuthController::class, 'hardDeleteAccount'])->middleware('auth')->name('settings.account.hard-delete');
Route::get('/orders', [WebOrderController::class, 'index'])->middleware(['auth', 'role:USER,COURIER'])->name('orders.index');
Route::get('/orders/create', [WebOrderController::class, 'create'])->middleware(['auth', 'role:USER'])->name('orders.create');
Route::post('/orders', [WebOrderController::class, 'store'])->middleware(['auth', 'role:USER'])->name('orders.store');
Route::get('/orders/feed', [WebOrderController::class, 'feed'])->middleware(['auth', 'role:COURIER'])->name('orders.feed');
Route::post('/orders/{order}/claim', [WebOrderController::class, 'claim'])->middleware(['auth', 'role:COURIER'])->name('orders.claim');
Route::post('/orders/{order}/status', [WebOrderController::class, 'updateStatus'])->middleware(['auth', 'role:COURIER'])->name('orders.status');
Route::post('/orders/{order}/complete', [WebOrderController::class, 'complete'])->middleware(['auth', 'role:USER'])->name('orders.complete');
Route::get('/wallet', [WebWalletController::class, 'index'])->middleware('auth')->name('wallet');
Route::get('/wallet/top-up', [WebWalletController::class, 'index'])->middleware('auth');
Route::post('/wallet/top-up', [WebWalletController::class, 'topUp'])->middleware('auth')->name('wallet.top-up');
Route::get('/trips', function (\Illuminate\Http\Request $request) { $sort=$request->query('sort','soonest'); $sortColumn=$sort==='destination'?'destination':'departure_at'; $direction=$sort==='latest'?'desc':'asc'; $trips=Trip::with('traveler')->withCount('conversations')->where('status','TRIP_OPEN')->when($request->filled('q'),fn($query)=>$query->where(fn($q)=>$q->where('code','like','%'.$request->q.'%')->orWhere('origin','like','%'.$request->q.'%')->orWhere('destination','like','%'.$request->q.'%')))->when($request->filled('destination'),fn($query)=>$query->where('destination',$request->destination))->when($request->filled('dp'),fn($query)=>$query->where('dp_percent',$request->dp))->orderBy($sortColumn,$direction)->paginate(10)->withQueryString(); return view('trips', ['trips'=>$trips]); })->middleware(['auth', 'role:USER,ADMIN,SUPER_ADMIN']);
Route::get('/trips/{trip}/chat', [WebTripChatController::class, 'show'])->middleware('auth')->name('trips.chat.show');
Route::post('/trips/{trip}/chat', [WebTripChatController::class, 'store'])->middleware('auth')->name('trips.chat.store');
Route::middleware(['auth', 'role:TRAVELER'])->prefix('traveler')->name('traveler.')->group(function () {
    Route::get('/', [WebTravelerController::class, 'index'])->name('dashboard');
    Route::post('/trips', [WebTravelerController::class, 'storeTrip'])->name('trips.store');
    Route::post('/orders/{order}/accept', [WebTravelerController::class, 'acceptOrder'])->name('orders.accept');
    Route::get('/feed', [WebTravelerController::class, 'feed'])->name('feed');
});
Route::get('/settings', function (\Illuminate\Http\Request $request) { $isSuperAdmin=Auth::check() && Auth::user()->role?->name === 'SUPER_ADMIN'; $activityQuery=ActivityLog::with('user')->when($request->filled('q'),fn($q)=>$q->where(fn($search)=>$search->where('description','like','%'.$request->q.'%')->orWhere('action','like','%'.$request->q.'%')->orWhere('ip_address','like','%'.$request->q.'%')->orWhere('route_name','like','%'.$request->q.'%')->orWhereHas('user',fn($user)=>$user->where('name','like','%'.$request->q.'%'))))->when($request->filled('action'),fn($q)=>$q->where('action',$request->action))->when($request->filled('entity_type'),fn($q)=>$q->where('entity_type',$request->entity_type)); $sort=$request->query('sort','latest'); $activityLogs=$isSuperAdmin ? $activityQuery->orderBy($sort==='action'?'action':'created_at',$sort==='action'?'asc':($sort==='oldest'?'asc':'desc'))->paginate(10)->withQueryString() : collect(); return view('settings', ['canViewActivityLogs'=>$isSuperAdmin,'activityLogs'=>$activityLogs,'activityActions'=>$isSuperAdmin ? ActivityLog::distinct()->orderBy('action')->pluck('action') : collect(),'activityEntities'=>$isSuperAdmin ? ActivityLog::whereNotNull('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type') : collect()]); });
// User and role management is an admin-only CRUD area. Keep authorization on
// the routes themselves so direct URLs and forged form submissions are blocked.
Route::middleware(['auth', 'role:ADMIN,SUPER_ADMIN'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::patch('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password.reset');
    Route::post('/roles', [UserController::class, 'storeRole'])->name('roles.store');
    Route::put('/roles/{role}', [UserController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{role}', [UserController::class, 'destroyRole'])->name('roles.destroy');
});
Route::get('/dashboard/{role}', function (string $role) {
    abort_unless(in_array($role, ['user','courier','traveler','admin','operator','super-admin'], true), 404);
    $requiredRole = $role === 'super-admin' ? 'SUPER_ADMIN' : strtoupper($role === 'admin' ? 'ADMIN' : $role);
    abort_unless(Auth::check() && Auth::user()->role?->name === $requiredRole, 403);

    if (in_array($role, ['super-admin', 'admin'], true)) {
        $ordersTrend = collect(range(5, 0))->map(function (int $monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            return [
                'label' => $date->format('M'),
                'value' => Order::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        });

        return view('super_admin_dashboard', [
            'title' => $role === 'super-admin' ? 'Dashboard Super Admin' : 'Dashboard Admin',
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

    if ($role === 'traveler') {
        return redirect('/traveler');
    }

    return view('dashboard', ['role' => strtoupper(str_replace('-', ' ', $role))]);
})->middleware('auth');
