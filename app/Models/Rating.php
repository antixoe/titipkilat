<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Rating extends Model { protected $fillable=['order_id','reviewer_id','reviewee_id','score','comment']; protected $casts=['score'=>'integer']; public function order(){return $this->belongsTo(Order::class);} }
