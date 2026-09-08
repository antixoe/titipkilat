<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FeeSetting extends Model { protected $fillable=['key','amount','description']; protected $casts=['amount'=>'integer']; public static function value(string $key,int $fallback=0):int{return (int)(static::where('key',$key)->value('amount')??$fallback);} }
