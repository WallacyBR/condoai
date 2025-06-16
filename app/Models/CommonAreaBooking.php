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
        'start_at',
        'end_at'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime'
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
