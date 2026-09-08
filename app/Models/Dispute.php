<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Dispute extends Model { protected $fillable=['order_id','user_id','reason','status','resolution']; public function order(){return $this->belongsTo(Order::class);} public function user(){return $this->belongsTo(User::class);} }
