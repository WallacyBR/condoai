<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdImage extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ad_id',
        'image_path',
        'order',
    ];

    /**
     * Get the ad that owns the AdImage.
     */
    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
