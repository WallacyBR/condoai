<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CondominiumScope;

class CommonArea extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope());
    }

    protected $fillable = [
        'name',
        'description',
        'rules',
        'photo_path',
        'condominium_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
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
