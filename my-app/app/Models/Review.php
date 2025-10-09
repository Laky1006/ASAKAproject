<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{

    protected $fillable = ['service_id', 'reguser_id', 'rating', 'comment'];
    protected $casts = [
    'created_at' => 'immutable_datetime:Y-m-d\TH:i:sP', // ISO with timezone
];
    public function reguser() {
        return $this->belongsTo(Reguser::class);
    }

    public function service() {
        return $this->belongsTo(Service::class);
    }
}
