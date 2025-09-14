<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'service_id',
        'service_title',
        'reguser_id',
        'reguser_name',
        'date',
        'time',
        'read',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function service() {
        return $this->belongsTo(Service::class);
    }

    public function reguser() {
        return $this->belongsTo(Reguser::class);
    }
}
