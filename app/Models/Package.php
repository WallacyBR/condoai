<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apartment;
use App\Models\User;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'apartment_id',
        'resident_id',
        'condominium_id',
        'recipient_name',
        'recipient_apartment',
        'recipient_phone',
        'sender',
        'description',
        'size',
        'weight',
        'tracking_code',
        'arrived_at',
        'delivered_at',
        'delivered_to',
        'is_delivered',
        'notes',
        'received_by',
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
        'arrived_at' => 'datetime',
        'delivered_at' => 'datetime',
        'retrieved_at' => 'datetime',
        'is_delivered' => 'boolean',
        'weight' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);

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

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
}
