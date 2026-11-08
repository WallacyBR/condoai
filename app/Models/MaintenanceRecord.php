<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CondominiumScope;

class MaintenanceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id',
        'maintenance_schedule_id',
        'user_id', // User who performed
        'performed_at',
        'notes',
        'status',
        'reviewed_by_user_id', // User who reviewed
        'reviewed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'status' => 'string', // Manter como string, já que o Enum é no DB
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

    public function schedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'maintenance_schedule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function photos()
    {
        return $this->hasMany(MaintenanceRecordPhoto::class);
    }

    public function signature()
    {
        // Assuming one signature per record for simplicity, change to hasMany if multiple are needed
        return $this->hasOne(MaintenanceRecordSignature::class);
    }
}
