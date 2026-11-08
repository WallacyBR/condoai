<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonAreaBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'common_area_id',
        'apartment_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function commonArea()
    {
        return $this->belongsTo(CommonArea::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
