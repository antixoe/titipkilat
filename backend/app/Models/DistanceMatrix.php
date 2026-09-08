<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DistanceMatrix extends Model { protected $fillable=['origin_zone','destination_zone','distance_km']; protected $casts=['distance_km'=>'integer']; }
