<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reguser extends Model
{
    //
    protected $fillable = ['user_id', 'grade'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_reguser')
            ->withPivot('date', 'time')
            ->withTimestamps();
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }
}
