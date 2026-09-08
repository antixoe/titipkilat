<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TripController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['origin'=>'required|string|max:120','destination'=>'required|string|max:120','departure_at'=>'required|date','arrival_at'=>'nullable|date|after:departure_at','dp_required'=>'required|boolean','dp_percent'=>'required|integer|min:0|max:100']);
        $trip = Trip::create(array_merge($data, ['traveler_id'=>$request->user()->id, 'code'=>'TRIP-'.Str::upper(Str::random(8)), 'status'=>'TRIP_OPEN']));
        return response()->json(['trip'=>$trip], 201);
    }

    public function index() { return response()->json(Trip::where('status','TRIP_OPEN')->with('traveler:id,name')->latest()->get()); }
}
