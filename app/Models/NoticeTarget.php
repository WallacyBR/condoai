<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticeTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'notice_id',
        'target_id',
        'target_type'
    ];

    public function notice()
    {
        return $this->belongsTo(Notice::class);
    }

    public function target()
    {
        return $this->morphTo();
    }
}
