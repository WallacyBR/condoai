<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CondominiumScope;

class MaintenanceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id',
        'maintenance_equipment_id',
        'title',
        'description',
        'type',
        'start_date',
        'end_date',
        'due_time',
        'is_recurring',
        'specific_days_of_week',
        'day_of_month',
        'month_of_year',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'due_time' => 'datetime:H:i', // Para garantir que seja tratado como hora
        'is_recurring' => 'boolean',
        'specific_days_of_week' => 'array', // Laravel trata JSON como array automaticamente
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

    public function equipment()
    {
        return $this->belongsTo(MaintenanceEquipment::class, 'maintenance_equipment_id');
    }

    public function records()
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    /**
     * Os usuários responsáveis por este agendamento de manutenção.
     */
    public function responsibleUsers()
    {
        return $this->belongsToMany(User::class, 'maintenance_schedule_user', 'maintenance_schedule_id', 'user_id')->withTimestamps();
    }
}
