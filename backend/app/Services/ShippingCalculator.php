<?php
namespace App\Services;
use App\Models\{DistanceMatrix,FeeSetting};
use Illuminate\Validation\ValidationException;
class ShippingCalculator { public function quote(string $origin,string $destination,float $weight):array { $matrix=DistanceMatrix::where('origin_zone',$origin)->where('destination_zone',$destination)->first(); if(!$matrix) throw ValidationException::withMessages(['destination_zone'=>'Rute belum tersedia pada distance matrix']); $weight=ceil($weight); $perLb=FeeSetting::value('shipping_base_per_lb',5000); $perKm=FeeSetting::value('shipping_per_km',100); return ['weight_lbs'=>$weight,'distance_km'=>$matrix->distance_km,'shipping_cost'=>(int)(($weight*$perLb)+($matrix->distance_km*$perKm))]; } }
