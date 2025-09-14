<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{

    protected $fillable = ['service_id', 'reguser_id', 'rating', 'comment'];
    public function reguser() {
        return $this->belongsTo(Reguser::class);
    }

    public function service() {
        return $this->belongsTo(Service::class);
    }
}
