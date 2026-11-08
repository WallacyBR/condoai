<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticeAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'notice_id',
        'file_path',
        'original_name',
        'file_type',
        'mime_type',
        'file_size',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the notice that owns the attachment.
     */
    public function notice()
    {
        return $this->belongsTo(Notice::class);
    }
}
