<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'sort_order'];
    public function users() { return $this->hasMany(User::class); }
}
