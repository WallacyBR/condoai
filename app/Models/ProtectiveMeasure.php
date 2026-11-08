<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProtectiveMeasure extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'protected_resident_id',
        'aggressor_visitor_id',
        'process_number',
        'measure_description',
        'document_url',
        'validity_start_date',
        'validity_end_date',
        'is_active',
        'condominium_id',
    ];

    protected $casts = [
        'validity_start_date' => 'date',
        'validity_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include protective measures of a given condominium.
     */
    public function scopeCondominium($query, $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    /**
     * Relacionamento com o morador protegido
     */
    public function protectedResident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'protected_resident_id');
    }

    /**
     * Relacionamento com o visitante agressor
     */
    public function aggressorVisitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class, 'aggressor_visitor_id');
    }

    /**
     * Relacionamento com o condomínio
     */
    public function condominiumRelation(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id');
    }
}
