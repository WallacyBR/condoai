<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CondominiumScope;

class Pet extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope('apartment'));
    }

    protected $fillable = [
        'name',
        'species',
        'breed',
        'photo_path',
        'apartment_id',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
