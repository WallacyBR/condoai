<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CondominiumScope;
use App\Enums\OccurrenceStatus;
use App\Enums\OccurrencePriority;

class Occurrence extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'user_id',
        'condominium_id',
    ];

    protected $casts = [
        'status' => OccurrenceStatus::class,
        'priority' => OccurrencePriority::class,
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function photos()
    {
        return $this->hasMany(OccurrencePhoto::class);
    }

    public function comments()
    {
        return $this->hasMany(OccurrenceComment::class);
    }
}
