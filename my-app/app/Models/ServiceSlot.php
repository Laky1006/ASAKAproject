<?php
// lesons DONE
//providers
// reguser

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'date',
        'time',
        'is_available',
        'reguser_id',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function reguser()
    {
        return $this->belongsTo(Reguser::class);
    }
}
