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

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
