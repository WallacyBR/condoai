<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CondominiumScope;

class MaintenanceRecordSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'maintenance_record_id',
        'signature_path',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope());
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
