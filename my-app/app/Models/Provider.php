<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    //
    protected $fillable = ['user_id', 'location', 'bio'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function services()
    {
        return $this->hasMany(\App\Models\Service::class);
    }
}
