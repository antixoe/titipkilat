<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EWallet extends Model { protected $fillable = ['user_id','balance']; protected $casts = ['balance'=>'integer']; public function user(){return $this->belongsTo(User::class);} public function transactions(){return $this->hasMany(WalletTransaction::class);} }
