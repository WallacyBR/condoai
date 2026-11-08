<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Assuming photos should also be scoped if accessed directly

class MaintenanceRecordPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'maintenance_record_id',
        'file_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * Apply condominium scope if these models can be queried directly.
     * If they are always accessed via MaintenanceRecord, this might be redundant
     * but adds safety for multi-tenancy.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function record()
    {
        return $this->belongsTo(MaintenanceRecord::class, 'maintenance_record_id');
    }
}
