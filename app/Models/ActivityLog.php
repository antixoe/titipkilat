<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id','action','description','entity_type','entity_id','metadata','ip_address','ip_location','user_agent','method','route_name'];
    protected $casts = ['metadata'=>'array'];
    public function user() { return $this->belongsTo(User::class); }

    protected static function booted(): void
    {
        static::creating(function (self $log) {
            if (!app()->runningInConsole() && request()) {
                $ip = request()->ip();
                $log->ip_address ??= $ip;
                $log->user_agent ??= request()->userAgent();
                $log->method ??= request()->method();
                $log->route_name ??= request()->route()?->getName() ?: request()->path();
                $log->ip_location ??= filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ? 'Public IP · lokasi perlu lookup' : 'Local / private network';
            }
        });
    }
}
