<?php
// lesons DONE
//providers
// reguser

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    //
    use HasFactory;
    protected $fillable = ['title', 'description', 'rating', 'provider_id', 'phone', 'banner'];

    protected $casts = [
        'date' => 'date',
        'labels' => 'array',
    ];

    public function provider()
    {
        return $this->belongsTo(\App\Models\Provider::class);
    }

    public function regusers()
    {
        return $this->belongsToMany(Reguser::class, 'service_reguser')
            ->withPivot('date', 'time')
            ->withTimestamps();
    }

    public function slots()
    {
        return $this->hasMany(\App\Models\ServiceSlot::class);
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }

    public function updateAverageRating()
    {
        $average = $this->reviews()->avg('rating');
        $this->rating = $average;
        $this->save();
    }

    public function savedByUsers()
    {
    return $this->hasMany(\App\Models\SavedService::class);
    }

}
