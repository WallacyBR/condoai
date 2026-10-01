<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str; // Para gerar o qr_code_hash

class MaintenanceEquipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maintenance_equipments';

    protected $fillable = [
        'condominium_id',
        'maintenance_area_id',
        'name',
        'description',
        'location_details',
        'qr_code_hash',
        'qr_code_access_level',
    ];

    protected $casts = [
        'qr_code_access_level' => 'string', // Manter como string, já que o Enum é no DB
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);

        // Gerar qr_code_hash ao criar, se não fornecido
        static::creating(function ($equipment) {
            if (empty($equipment->qr_code_hash)) {
                $equipment->qr_code_hash = Str::random(32); // Gera um hash aleatório
            }
        });
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function maintenanceArea()
    {
        return $this->belongsTo(MaintenanceArea::class);
    }

    public function schedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function qrAccessUsers()
    {
        return $this->belongsToMany(User::class, 'maintenance_qr_access_users', 'maintenance_equipment_id', 'user_id');
    }

    /**
     * Accessor para obter usuários com acesso QR (apenas quando o relacionamento estiver carregado)
     */
    public function getQrAccessUsersAttribute()
    {
        if ($this->relationLoaded('qrAccessUsers')) {
            return $this->qrAccessUsers;
        }

        return [];
    }
}
