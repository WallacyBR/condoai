<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'image_path',
        'condominium_id'
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function noticeTargets()
    {
        return $this->hasMany(NoticeTarget::class);
    }

    public function towers()
    {
        return $this->morphedByMany(Tower::class, 'target', 'notice_targets');
    }

    public function blocks()
    {
        return $this->morphedByMany(Block::class, 'target', 'notice_targets');
    }

    public function apartments()
    {
        return $this->morphedByMany(Apartment::class, 'target', 'notice_targets');
    }
}
