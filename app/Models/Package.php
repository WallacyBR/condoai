<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apartment;
use App\Models\User;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'apartment_id',
        'photo',
        'pickup_location',
        'observation',
        'user_id',
        'signature',
        'document_photo',
        'code',
        'retrieved_at',
    ];

    protected $casts = [
        'retrieved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($package) {
            if (empty($package->code)) {
                $package->code = strtoupper(substr(md5(uniqid((string) random_int(1, 9999), true)), 0, 8));
            }
        });
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
