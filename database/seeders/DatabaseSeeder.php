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
        \App\Models\Trip::where('code','like','TRIP-DEMO%')->whereNotIn('code',['TRIP-DEMO001','TRIP-DEMO002','TRIP-DEMO003','TRIP-DEMO004','TRIP-DEMO005'])->delete();
        $tripProducts = [
            ['Tokyo Beauty Haul','Skincare Jepang, sunscreen, sheet mask, dan makeup limited edition.','/images/trips/tokyo-beauty.svg',[['name'=>'Anessa Sunscreen','description'=>'Sunscreen SPF50+ ukuran 60ml.','price'=>185000],['name'=>'Sheet Mask Set','description'=>'Paket 7 sheet mask Jepang untuk perawatan mingguan.','price'=>120000],['name'=>'Canmake Blush','description'=>'Blush compact warna natural, pilihan warna tersedia.','price'=>145000]]],
            ['Singapore Snack Box','Camilan premium, kopi lokal, dan oleh-oleh khas Singapura.','/images/trips/singapore-snacks.svg',[['name'=>'Irvin’s Salted Egg','description'=>'Keripik salted egg ukuran 105 gram.','price'=>135000],['name'=>'Kopi Singapore','description'=>'Kopi lokal premium dalam kemasan oleh-oleh.','price'=>95000],['name'=>'Snack Mix Box','description'=>'Campuran camilan populer dalam satu box.','price'=>220000]]],
            ['Seoul K-Beauty','Lip tint, cushion, toner, dan produk skincare Korea yang sedang tren.','/images/trips/seoul-kbeauty.svg',[['name'=>'Rom&nd Lip Tint','description'=>'Lip tint glossy dengan pilihan warna Korea terbaru.','price'=>155000],['name'=>'Toner Hydrating','description'=>'Toner ringan untuk kulit kering dan kombinasi.','price'=>190000],['name'=>'Cushion Compact','description'=>'Cushion natural finish dengan refill.','price'=>245000]]],
            ['Bangkok Street Finds','Fashion streetwear, aksesori, dan produk lokal Chatuchak.','/images/trips/bangkok-fashion.svg',[['name'=>'Streetwear Tee','description'=>'Kaos oversized cotton dari brand lokal Bangkok.','price'=>175000],['name'=>'Canvas Tote','description'=>'Tas kanvas kuat untuk pemakaian harian.','price'=>110000],['name'=>'Handmade Bracelet','description'=>'Gelang handmade dengan motif Thailand.','price'=>80000]]],
            ['Tokyo Collectibles','Figure anime, stationery, dan collectible resmi dari Jepang.','/images/trips/tokyo-collectibles.svg',[['name'=>'Anime Figure','description'=>'Figure resmi blind box, karakter mengikuti stok toko.','price'=>280000],['name'=>'Stationery Bundle','description'=>'Paket notebook dan pena Jepang untuk kolektor.','price'=>125000],['name'=>'Collector Pin Set','description'=>'Set pin enamel edisi terbatas.','price'=>165000]]],
        ];
        foreach (range(1,5) as $i) { $traveler=$travelers[($i-1) % $travelers->count()]; $product=$tripProducts[$i-1]; \App\Models\Trip::updateOrCreate(['code'=>"TRIP-DEMO".str_pad($i,3,'0',STR_PAD_LEFT)],['traveler_id'=>$traveler->id,'origin'=>'Jakarta','destination'=>['Tokyo','Singapore','Seoul','Bangkok','Tokyo'][$i-1],'product_details'=>$product[1],'product_image'=>$product[2],'product_items'=>$product[3],'departure_at'=>now()->addDays($i),'arrival_at'=>now()->addDays($i+1),'dp_required'=>$i !== 3,'dp_percent'=>$i === 3 ? 0 : ($i % 2 === 0 ? 30 : 50),'status'=>'TRIP_OPEN']); }
        foreach (range(1,20) as $i) { $customer=$customers[$i % $customers->count()]; $courier=$couriers[$i % $couriers->count()]; \App\Models\Order::firstOrCreate(['item_description'=>"Demo item {$i}"],['customer_id'=>$customer->id,'courier_id'=>$courier->id,'type'=>'ANTAR_WARGA','status'=>$i % 4 === 0 ? 'COMPLETED' : 'SEARCHING_COURIER','origin_zone'=>'JAKARTA','destination_zone'=>$i % 2 === 0 ? 'BANDUNG' : 'SURABAYA','weight_lbs'=>($i % 5)+1,'pickup_address'=>'Jl. Demo Pickup '.$i,'delivery_address'=>'Jl. Demo Delivery '.$i,'item_count'=>1,'item_cost'=>($i+1)*25000,'shipping_cost'=>5000,'commission'=>1000,'courier_fee'=>1000]); }
        foreach (range(1,20) as $i) { $wallet=\App\Models\EWallet::where('user_id',$demoUsers[$i-1]->id)->first(); \App\Models\WalletTransaction::firstOrCreate(['reference'=>"DEMO-TX-{$i}"],['e_wallet_id'=>$wallet->id,'type'=>'TOP_UP','debit'=>0,'credit'=>10000*$i,'amount'=>10000*$i,'fee'=>1000,'description'=>'Demo top up']); }

        $testUser = User::withTrashed()->updateOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'password' => 'password',
            'role_id' => $roleIds['USER'],
        ]);
        $testUser->restore();
    }
}
