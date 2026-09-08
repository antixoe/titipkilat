<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\{Order,Rating}; use Illuminate\Http\Request;
class RatingController extends Controller { public function store(Request $r,Order $order){abort_unless((int)$order->customer_id===(int)$r->user()->id,403,'Hanya customer yang dapat memberi rating');if($order->status!=='COMPLETED'||(!$order->courier_id&&!$order->traveler_id))return response()->json(['message'=>'Order belum dapat diberi rating'],422);$d=$r->validate(['score'=>'required|integer|min:1|max:5','comment'=>'nullable|string|max:1000']);$rating=Rating::create(array_merge($d,['order_id'=>$order->id,'reviewer_id'=>$r->user()->id,'reviewee_id'=>$order->courier_id?:$order->traveler_id]));return response()->json(['rating'=>$rating],201);} }
