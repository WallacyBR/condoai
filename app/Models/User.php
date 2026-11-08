<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'profile_id',
        'condominium_id',
        'notification_email',
        'notification_push',
        'notification_sms',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'notification_email' => 'boolean',
        'notification_push' => 'boolean',
        'notification_sms' => 'boolean',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            if ($user->condominium_id !== null) {
                $user->condominiums()->syncWithoutDetaching([$user->condominium_id]);
            }
        });
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function condominiums()
    {
        return $this->belongsToMany(Condominium::class, 'condominium_user')
            ->withTimestamps();
    }

    public function belongsToCondominium(int $condominiumId): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        return $this->condominiums()
            ->where('condominiums.id', $condominiumId)
            ->exists();
    }

    public function isPlatformAdmin(): bool
    {
        return $this->profile?->name === 'SysOp';
    }

    /**
     * Os agendamentos de manutenção pelos quais este usuário é responsável.
     */
    public function maintenanceSchedules()
    {
        return $this->belongsToMany(MaintenanceSchedule::class, 'maintenance_schedule_user', 'user_id', 'maintenance_schedule_id')->withTimestamps();
    }

    public function solicitacoesAcessoDestinadas()
    {
        return $this->hasMany(SolicitacaoAcesso::class, 'morador_destino_id');
    }

    public function medidasProtetivasComoProtegido()
    {
        return $this->hasMany(MedidaProtetiva::class, 'morador_protegido_id');
    }
}
