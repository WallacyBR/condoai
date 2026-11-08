<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccurrenceComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'occurrence_id',
        'user_id',
        'comment',
    ];

    public function occurrence()
    {
        return $this->belongsTo(Occurrence::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
