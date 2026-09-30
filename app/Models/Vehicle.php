<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope('apartment'));
    }

    protected $fillable = [
        'license_plate',
        'model',
        'color',
        'apartment_id',
        'parking_spot_id',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function parkingSpot()
    {
        return $this->belongsTo(ParkingSpot::class);
    }
}
