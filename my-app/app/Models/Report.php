<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'review_id',
        'service_id',
        'reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
