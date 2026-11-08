<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccurrencePhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'occurrence_id',
        'photo_path',
        'description',
    ];

    public function occurrence()
    {
        return $this->belongsTo(Occurrence::class);
    }
}
