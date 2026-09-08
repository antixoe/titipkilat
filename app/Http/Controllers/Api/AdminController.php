<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\{Dispute,Order,User}; use Illuminate\Http\Request;
class AdminController extends Controller
{
 public function activeTransactions(){return response()->json(Order::where('status','!=','COMPLETED')->with('customer:id,name','courier:id,name','traveler:id,name')->latest()->paginate(25));}
 public function disputes(){return response()->json(Dispute::with(['order','user:id,name,email'])->where('status','OPEN')->latest()->paginate(25));}
 public function resolveDispute(Request $r,Dispute $dispute){$d=$r->validate(['status'=>'required|in:RESOLVED,REJECTED','resolution'=>'required|string|max:2000']);$dispute->update($d);return response()->json(['dispute'=>$dispute->fresh('order','user')]);}
 public function verifyKyc(Request $r,User $user){$d=$r->validate(['kyc_status'=>'required|in:VERIFIED,REJECTED']);$user->update(['kyc_status'=>$d['kyc_status']]);return response()->json(['user'=>$user->fresh()->makeVisible('kyc_status')]);}
 public function submitDispute(Request $r,Order $order){$d=$r->validate(['reason'=>'required|string|max:2000']);abort_unless((int)$order->customer_id===(int)$r->user()->id,403,'Hanya pemilik order yang dapat membuat dispute');$dispute=Dispute::create(['order_id'=>$order->id,'user_id'=>$r->user()->id,'reason'=>$d['reason'],'status'=>'OPEN']);return response()->json(['dispute'=>$dispute],201);}
}
