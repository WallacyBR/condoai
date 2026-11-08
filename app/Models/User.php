<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    public const STAFF_PROFILE_NAMES = ['SysOp', 'Administradora', 'Síndico', 'Zelador'];

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

    public function passkeys()
    {
        return $this->hasMany(UserPasskey::class);
    }

    public function belongsToCondominium(int $condominiumId): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        if ($this->condominium_id && (int) $this->condominium_id === $condominiumId) {
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

    public function isSuperAdmin(): bool
    {
        if ((bool) ($this->is_super_admin ?? false)) {
            return true;
        }

        if ($this->isPlatformAdmin()) {
            return true;
        }

        $profileName = Str::of($this->profile?->name ?? '')
            ->ascii()
            ->lower()
            ->toString();

        return in_array($profileName, ['sysop', 'super admin', 'super administrador'], true);
    }

    public function hasStaffProfile(): bool
    {
        return in_array($this->profile?->name, self::STAFF_PROFILE_NAMES, true);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function residentInCondominium(int $condominiumId): ?Resident
    {
        return $this->residents()
            ->withoutGlobalScopes()
            ->where('condominium_id', $condominiumId)
            ->first();
    }

    public function canModule(string $moduleName, string $ability): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        $pivot = $this->modulePivot($moduleName);

        if ($pivot === null) {
            return false;
        }

        return match ($ability) {
            'view' => (bool) $pivot->can_view,
            'create' => (bool) $pivot->can_create,
            'update' => (bool) $pivot->can_update,
            'delete' => (bool) $pivot->can_delete,
            default => false,
        };
    }

    public function moduleScope(string $moduleName): ?string
    {
        if ($this->isPlatformAdmin()) {
            return 'all';
        }

        return $this->modulePivot($moduleName)?->scope;
    }

    private function modulePivot(string $moduleName): ?object
    {
        if ($this->profile === null) {
            return null;
        }

        $module = $this->profile->modules()
            ->where('modules.name', $moduleName)
            ->first();

        return $module?->pivot;
    }

    public function hasPermissionTo(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        [$action, $moduleKey] = array_pad(explode('_', $permission, 2), 2, '');

        $permissionColumn = [
            'view' => 'can_view',
            'create' => 'can_create',
            'edit' => 'can_update',
            'update' => 'can_update',
            'delete' => 'can_delete',
            'approve' => 'can_update',
            'reconcile' => 'can_update',
        ][$action] ?? null;

        if (! $permissionColumn || ! $moduleKey) {
            return false;
        }

        $moduleAlias = [
            'occurrences' => 'ocorrencias',
            'packages' => 'correspondencias',
            'residents' => 'moradores',
            'units' => 'unidades',
            'payables' => 'financeiro',
            'receivables' => 'financeiro',
            'security' => 'seguranca',
        ][$moduleKey] ?? $moduleKey;

        $profile = $this->relationLoaded('profile') ? $this->profile : $this->profile()->with('modules')->first();

        if (! $profile) {
            return false;
        }

        $modules = $profile->relationLoaded('modules') ? $profile->modules : $profile->modules()->get();

        return $modules->contains(function ($module) use ($moduleAlias, $permissionColumn) {
            $normalizedModuleName = Str::of($module->name)
                ->ascii()
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '')
                ->toString();

            $normalizedAlias = Str::of($moduleAlias)
                ->ascii()
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '')
                ->toString();

            return $normalizedModuleName === $normalizedAlias && (bool) $module->pivot->{$permissionColumn};
        });
    }

    /**
     * Os agendamentos de manutenção pelos quais este usuário é responsável.
     */
    public function maintenanceSchedules()
    {
        return $this->belongsToMany(MaintenanceSchedule::class, 'maintenance_schedule_user', 'user_id', 'maintenance_schedule_id')->withTimestamps();
    }

    public function protectiveMeasuresAsProtected()
    {
        return $this->hasMany(ProtectiveMeasure::class, 'protected_resident_id');
    }
}
