<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WalletTransaction extends Model { protected $fillable = ['e_wallet_id','type','debit','credit','amount','fee','description','reference']; protected $casts = ['debit'=>'integer','credit'=>'integer','amount'=>'integer','fee'=>'integer']; public function wallet(){return $this->belongsTo(EWallet::class,'e_wallet_id');} }
