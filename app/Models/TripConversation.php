<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripConversation extends Model
{
    protected $fillable = ['trip_id', 'customer_id'];

    public function trip() { return $this->belongsTo(Trip::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function messages() { return $this->hasMany(TripMessage::class, 'conversation_id'); }
}
