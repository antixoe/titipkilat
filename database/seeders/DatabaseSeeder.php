<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['USER','COURIER','TRAVELER','ADMIN','OPERATOR','SUPER_ADMIN'] as $name) { \App\Models\Role::firstOrCreate(['name' => $name]); }
        $roleIds = \App\Models\Role::pluck('id', 'name');
        foreach ([['Budi User','user@titipkilat.test','USER'],['Citra Kurir','courier@titipkilat.test','COURIER'],['Dewi Traveler','traveler@titipkilat.test','TRAVELER'],['Admin Titip','admin@titipkilat.test','ADMIN'],['Operator Titip','operator@titipkilat.test','OPERATOR']] as [$name,$email,$role]) {
            $user = \App\Models\User::updateOrCreate(['email'=>$email],['name'=>$name,'password'=>'password','role_id'=>$roleIds[$role]]);
            $wallet = \App\Models\EWallet::firstOrCreate(['user_id'=>$user->id],['balance'=>100000]);
            if ($wallet->wasRecentlyCreated) { \App\Models\WalletTransaction::create(['e_wallet_id'=>$wallet->id,'type'=>'OPENING_BALANCE','debit'=>0,'credit'=>100000,'amount'=>100000,'description'=>'Saldo awal mock']); }
        }
        $super = \App\Models\User::updateOrCreate(['email'=>'superadmin@titipkilat.test'],['name'=>'Super Admin Titip','password'=>'password','role_id'=>$roleIds['SUPER_ADMIN']]);
        \App\Models\EWallet::updateOrCreate(['user_id'=>$super->id],['balance'=>0]);
        foreach ([['top_up_fee',1000,'Biaya layanan top up'],['app_commission',1000,'Komisi aplikasi per order'],['courier_fee',1000,'Fee kurir per item']] as [$key,$amount,$description]) { \App\Models\FeeSetting::firstOrCreate(['key'=>$key],compact('amount','description')); }
        foreach ([['JAKARTA','BANDUNG',150],['BANDUNG','JAKARTA',150],['JAKARTA','SURABAYA',780],['SURABAYA','JAKARTA',780],['BANDUNG','SURABAYA',680]] as [$origin,$destination,$distance_km]) { \App\Models\DistanceMatrix::firstOrCreate(['origin_zone'=>$origin,'destination_zone'=>$destination],['distance_km'=>$distance_km]); }
        foreach ([['shipping_base_per_lb',5000,'Komponen ongkir per pound'],['shipping_per_km',100,'Komponen ongkir per kilometer']] as [$key,$amount,$description]) { \App\Models\FeeSetting::firstOrCreate(['key'=>$key],compact('amount','description')); }

        $travelerRole = $roleIds['TRAVELER']; $userRole = $roleIds['USER']; $courierRole = $roleIds['COURIER']; $demoUsers = [];
        for ($i=1; $i<=20; $i++) { $role = $i % 5 === 0 ? $courierRole : ($i % 4 === 0 ? $travelerRole : $userRole); $demoUsers[] = \App\Models\User::updateOrCreate(['email'=>"demo{$i}@titipkilat.test"],['name'=>"Demo User {$i}",'password'=>'password','role_id'=>$role,'kyc_status'=>$i % 3 === 0 ? 'VERIFIED' : 'PENDING']); }
        $travelers = collect($demoUsers)->filter(fn($user) => (int)$user->role_id === (int)$travelerRole)->values(); $customers = collect($demoUsers)->filter(fn($user) => (int)$user->role_id === (int)$userRole)->values(); $couriers = collect($demoUsers)->filter(fn($user) => (int)$user->role_id === (int)$courierRole)->values();
        foreach ($demoUsers as $user) { \App\Models\EWallet::firstOrCreate(['user_id'=>$user->id],['balance'=>100000]); }
        foreach (range(1,20) as $i) { $traveler=$travelers[$i % $travelers->count()]; \App\Models\Trip::updateOrCreate(['code'=>"TRIP-DEMO".str_pad($i,3,'0',STR_PAD_LEFT)],['traveler_id'=>$traveler->id,'origin'=>'Jakarta','destination'=>['Tokyo','Singapore','Seoul','Bangkok'][$i % 4],'departure_at'=>now()->addDays($i),'arrival_at'=>now()->addDays($i+1),'dp_required'=>$i % 3 !== 0,'dp_percent'=>$i % 3 === 0 ? 0 : ($i % 2 === 0 ? 30 : 50),'status'=>'TRIP_OPEN']); }
        foreach (range(1,20) as $i) { $customer=$customers[$i % $customers->count()]; $courier=$couriers[$i % $couriers->count()]; \App\Models\Order::firstOrCreate(['item_description'=>"Demo item {$i}"],['customer_id'=>$customer->id,'courier_id'=>$courier->id,'type'=>'ANTAR_WARGA','status'=>$i % 4 === 0 ? 'COMPLETED' : 'SEARCHING_COURIER','origin_zone'=>'JAKARTA','destination_zone'=>$i % 2 === 0 ? 'BANDUNG' : 'SURABAYA','weight_lbs'=>($i % 5)+1,'pickup_address'=>'Jl. Demo Pickup '.$i,'delivery_address'=>'Jl. Demo Delivery '.$i,'item_count'=>1,'item_cost'=>($i+1)*25000,'shipping_cost'=>5000,'commission'=>1000,'courier_fee'=>1000]); }
        foreach (range(1,20) as $i) { $wallet=\App\Models\EWallet::where('user_id',$demoUsers[$i-1]->id)->first(); \App\Models\WalletTransaction::firstOrCreate(['reference'=>"DEMO-TX-{$i}"],['e_wallet_id'=>$wallet->id,'type'=>'TOP_UP','debit'=>0,'credit'=>10000*$i,'amount'=>10000*$i,'fee'=>1000,'description'=>'Demo top up']); }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
