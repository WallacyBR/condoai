<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonAreaBooking extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'common_area_id',
        'apartment_id',
        'resident_id',
        'condominium_id',
        'booking_date',
        'start_time',
        'end_time',
        'duration_hours',
        'purpose',
        'guest_count',
        'total_amount',
        'payment_status',
        'status',
        'special_requirements',
        'cleaning_fee',
        'deposit_amount',
        'booking_rules_accepted',
        'confirmation_date',
        'cancellation_reason',
        'cancelled_at',
        'booking_type',
        'total_price', // Mantido para compatibilidade
        'notes',
        'receivable_id',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_amount' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'booking_rules_accepted' => 'boolean',
        'confirmation_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    // Constantes para tipos de reserva
    const TYPE_CUSTOM = 'custom';

    const TYPE_MORNING = 'morning';

    const TYPE_AFTERNOON = 'afternoon';

    const TYPE_EVENING = 'evening';

    const TYPE_FULL_DAY = 'full_day';

    // Constantes para status
    const STATUS_PENDING = 'pending';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_COMPLETED = 'completed';

    public function commonArea()
    {
        return $this->belongsTo(CommonArea::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function receivable()
    {
        return $this->belongsTo(Receivable::class);
    }

    // Obter nome do tipo de reserva
    public function getBookingTypeName()
    {
        $types = [
            self::TYPE_CUSTOM => 'Personalizada',
            self::TYPE_MORNING => 'Manhã',
            self::TYPE_AFTERNOON => 'Tarde',
            self::TYPE_EVENING => 'Noite',
            self::TYPE_FULL_DAY => 'Dia Todo',
        ];

        return $types[$this->booking_type] ?? 'Desconhecido';
    }

    // Obter nome do status
    public function getStatusName()
    {
        $statuses = [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_CONFIRMED => 'Confirmada',
            self::STATUS_CANCELLED => 'Cancelada',
            self::STATUS_COMPLETED => 'Concluída',
        ];

        return $statuses[$this->status] ?? 'Desconhecido';
    }

    // Obter classe CSS para o status
    public function getStatusClass()
    {
        $classes = [
            self::STATUS_PENDING => 'text-yellow-500',
            self::STATUS_CONFIRMED => 'text-green-500',
            self::STATUS_CANCELLED => 'text-red-500',
            self::STATUS_COMPLETED => 'text-blue-500',
        ];

        return $classes[$this->status] ?? 'text-gray-500';
    }

    // Calcular duração da reserva em horas
    public function getDurationInHours()
    {
        return $this->start_time->diffInHours($this->end_time);
    }

    // Verificar se a reserva está ativa (não cancelada)
    public function isActive()
    {
        return $this->status !== self::STATUS_CANCELLED;
    }

    // Verificar se a reserva pode ser cancelada
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]) &&
               $this->start_time->isFuture();
    }

    // Verificar se a reserva pode ser editada
    public function canBeEdited()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]) &&
               $this->start_time->isFuture();
    }

    // Scope para reservas ativas
    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    // Scope para reservas futuras
    public function scopeFuture($query)
    {
        return $query->where('start_time', '>', now());
    }

    // Scope para reservas passadas
    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }

    // Scope para reservas de hoje
    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }
}
