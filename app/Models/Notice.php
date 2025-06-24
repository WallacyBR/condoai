<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id',
        'user_id',
        'title',
        'body',
        'send_to_all',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'send_to_all' => 'boolean',
    ];

    /**
     * Get the condominium that owns the notice.
     */
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    /**
     * Get the user who created the notice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the towers that are assigned this notice.
     */
    public function towers()
    {
        return $this->morphToMany(
            Tower::class,
            'noticable',
            'noticables',
            'notice_id',
            'noticable_id'
        );
    }

    /**
     * Get all of the blocks that are assigned this notice.
     */
    public function blocks()
    {
        return $this->morphToMany(
            Block::class,
            'noticable',
            'noticables',
            'notice_id',
            'noticable_id'
        );
    }

    /**
     * Get all of the apartments that are assigned this notice.
     */
    public function apartments()
    {
        return $this->morphToMany(
            Apartment::class,
            'noticable',
            'noticables',
            'notice_id',
            'noticable_id'
        );
    }

    /**
     * Get the attachments for the notice.
     */
    public function attachments()
    {
        // Este relacionamento será com NoticeAttachment, que ainda será criado.
        // Adicionando como placeholder para completar a estrutura do model.
        return $this->hasMany(NoticeAttachment::class);
    }
}
