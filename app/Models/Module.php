<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function profiles()
    {
        return $this->belongsToMany(Profile::class)
            ->withPivot(['can_view', 'can_create', 'can_update', 'can_delete', 'scope'])
            ->withTimestamps();
    }
}
