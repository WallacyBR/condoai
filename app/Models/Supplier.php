<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['condominium_id', 'name', 'document', 'email', 'phone'];

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }
}
