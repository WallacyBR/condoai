<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'condominium_id',
        'capacity',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function bookings()
    {
        return $this->hasMany(CommonAreaBooking::class);
    }
}
