<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = ['traveler_id', 'code', 'origin', 'destination', 'departure_at', 'arrival_at', 'dp_required', 'dp_percent', 'status', 'product_details', 'product_image', 'product_items'];
    protected $casts = ['departure_at' => 'datetime', 'arrival_at' => 'datetime', 'dp_required' => 'boolean', 'dp_percent' => 'integer', 'product_items' => 'array'];
    public function traveler() { return $this->belongsTo(User::class, 'traveler_id'); }
    public function conversations() { return $this->hasMany(TripConversation::class); }
}
