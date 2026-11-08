<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementRequest extends Model
{
    use HasFactory;

    protected $fillable = ['condominium_id', 'created_by', 'code', 'title', 'description', 'response_deadline', 'status', 'selection_criterion', 'selection_justification', 'publish_result', 'published_at', 'closed_at'];

    protected $casts = ['response_deadline' => 'date', 'publish_result' => 'boolean', 'published_at' => 'datetime', 'closed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    public function items()
    {
        return $this->hasMany(ProcurementItem::class)->orderBy('position');
    }

    public function invitations()
    {
        return $this->hasMany(ProcurementInvitation::class);
    }

    public function awards()
    {
        return $this->hasMany(ProcurementAward::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
