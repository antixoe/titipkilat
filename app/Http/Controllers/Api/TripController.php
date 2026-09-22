<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TripController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['origin'=>'required|string|max:120','destination'=>'required|string|max:120','departure_at'=>'required|date','arrival_at'=>'nullable|date|after:departure_at','dp_required'=>'required|boolean','dp_percent'=>'required|integer|min:0|max:100','product_details'=>'nullable|string|max:2000','product_image'=>'nullable|image|max:5120','product_items'=>'nullable|array|max:20','product_items.*.name'=>'required_with:product_items|string|max:120','product_items.*.description'=>'nullable|string|max:500','product_items.*.price'=>'required_with:product_items|integer|min:0']);
        if ($request->hasFile('product_image')) $data['product_image'] = Storage::disk('public')->url($request->file('product_image')->store('trip-products', 'public'));
        $trip = Trip::create(array_merge($data, ['traveler_id'=>$request->user()->id, 'code'=>'TRIP-'.Str::upper(Str::random(8)), 'status'=>'TRIP_OPEN']));
        return response()->json(['trip'=>$trip], 201);
    }

    public function index() { return response()->json(Trip::where('status','TRIP_OPEN')->with('traveler:id,name,email')->latest()->get()); }
}
