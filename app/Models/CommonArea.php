<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CommonArea extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'name',
        'description',
        'condominium_id',
        'capacity',
        'main_photo',
        'additional_photos',
        'active',
        'availability_schedule',
        'unavailability_periods',
        'price_per_hour',
        'rules',
        'min_booking_hours',
        'max_booking_hours',
        'requires_approval',
        'booking_periods',
        'allow_custom_periods',
        'booking_schedule',
        'max_bookings_per_day',
        'max_bookings_per_month',
        'max_bookings_per_year',
    ];

    protected $casts = [
        'active' => 'boolean',
        'additional_photos' => 'array',
        'availability_schedule' => 'array',
        'unavailability_periods' => 'array',
        'booking_periods' => 'array',
        'booking_schedule' => 'array',
        'price_per_hour' => 'decimal:2',
        'requires_approval' => 'boolean',
        'allow_custom_periods' => 'boolean',
    ];

    // Constantes para tipos de reserva
    const BOOKING_TYPE_HOURLY = 'hourly';
    const BOOKING_TYPE_PERIOD = 'period';

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function bookings()
    {
        return $this->hasMany(CommonAreaBooking::class);
    }

    // Obter configuração padrão de agendamento por dia da semana
    public function getDefaultBookingSchedule()
    {
        if (!$this->booking_schedule) {
            return [
                0 => ['type' => self::BOOKING_TYPE_PERIOD, 'enabled' => true], // Domingo
                1 => ['type' => self::BOOKING_TYPE_PERIOD, 'enabled' => true], // Segunda
                2 => ['type' => self::BOOKING_TYPE_PERIOD, 'enabled' => true], // Terça
                3 => ['type' => self::BOOKING_TYPE_PERIOD, 'enabled' => true], // Quarta
                4 => ['type' => self::BOOKING_TYPE_PERIOD, 'enabled' => true], // Quinta
                5 => ['type' => self::BOOKING_TYPE_PERIOD, 'enabled' => true], // Sexta
                6 => ['type' => self::BOOKING_TYPE_PERIOD, 'enabled' => true], // Sábado
            ];
        }

        return $this->booking_schedule;
    }

    // Obter tipo de reserva para um dia específico
    public function getBookingTypeForDay($dayOfWeek)
    {
        $schedule = $this->getDefaultBookingSchedule();
        
        if (!isset($schedule[$dayOfWeek]) || !$schedule[$dayOfWeek]['enabled']) {
            return null; // Dia não disponível
        }

        return $schedule[$dayOfWeek]['type'];
    }

    // Obter períodos padrão configurados
    public function getDefaultBookingPeriods()
    {
        if (!$this->booking_periods) {
            return [
                'morning' => [
                    'name' => 'Manhã',
                    'start_time' => '08:00',
                    'end_time' => '12:00',
                    'price' => $this->price_per_hour ? $this->price_per_hour * 4 : null,
                    'enabled' => true
                ],
                'afternoon' => [
                    'name' => 'Tarde',
                    'start_time' => '13:00',
                    'end_time' => '17:00',
                    'price' => $this->price_per_hour ? $this->price_per_hour * 4 : null,
                    'enabled' => true
                ],
                'evening' => [
                    'name' => 'Noite',
                    'start_time' => '18:00',
                    'end_time' => '22:00',
                    'price' => $this->price_per_hour ? $this->price_per_hour * 4 : null,
                    'enabled' => true
                ],
                'full_day' => [
                    'name' => 'Dia Todo',
                    'start_time' => '08:00',
                    'end_time' => '22:00',
                    'price' => $this->price_per_hour ? $this->price_per_hour * 14 : null,
                    'enabled' => true
                ]
            ];
        }

        return $this->booking_periods;
    }

    // Verificar se um período específico está disponível
    public function isPeriodAvailable($date, $periodType)
    {
        $periods = $this->getDefaultBookingPeriods();
        
        if (!isset($periods[$periodType]) || !$periods[$periodType]['enabled']) {
            return false;
        }

        $period = $periods[$periodType];
        $startTime = $date . ' ' . $period['start_time'];
        $endTime = $date . ' ' . $period['end_time'];

        return $this->isAvailableAt($startTime, $endTime);
    }

    // Obter slots disponíveis para um período específico
    public function getAvailablePeriodSlots($date)
    {
        $availableSlots = [];
        $periods = $this->getDefaultBookingPeriods();

        foreach ($periods as $periodType => $period) {
            if ($period['enabled'] && $this->isPeriodAvailable($date, $periodType)) {
                $availableSlots[] = [
                    'type' => $periodType,
                    'name' => $period['name'],
                    'start_time' => $period['start_time'],
                    'end_time' => $period['end_time'],
                    'price' => $period['price'],
                    'formatted_time' => "{$period['start_time']} - {$period['end_time']}"
                ];
            }
        }

        return $availableSlots;
    }

    // Calcular preço para um período específico
    public function calculatePeriodPrice($periodType, $customHours = null)
    {
        if ($periodType === 'custom' && $customHours && $this->price_per_hour) {
            return $this->price_per_hour * $customHours;
        }

        $periods = $this->getDefaultBookingPeriods();
        return $periods[$periodType]['price'] ?? null;
    }

    // Verificar se a área está disponível em um determinado período
    public function isAvailableAt($startTime, $endTime)
    {
        if (!$this->active) {
            return false;
        }

        // Verificar conflitos com reservas existentes
        $hasConflict = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();

        if ($hasConflict) {
            return false;
        }

        // Verificar horários de funcionamento
        if ($this->availability_schedule) {
            $dayOfWeek = date('w', strtotime($startTime)); // 0 = domingo, 1 = segunda, etc.
            $schedule = $this->availability_schedule;
            
            if (!isset($schedule[$dayOfWeek]) || !$schedule[$dayOfWeek]['enabled']) {
                return false;
            }
            
            $startHour = date('H:i', strtotime($startTime));
            $endHour = date('H:i', strtotime($endTime));
            $daySchedule = $schedule[$dayOfWeek];
            
            if ($startHour < $daySchedule['start_time'] || $endHour > $daySchedule['end_time']) {
                return false;
            }
        }

        // Verificar períodos de indisponibilidade
        if ($this->unavailability_periods) {
            foreach ($this->unavailability_periods as $period) {
                if ($startTime >= $period['start_date'] && $endTime <= $period['end_date']) {
                    return false;
                }
            }
        }

        return true;
    }

    // Obter horários disponíveis para um dia específico (incluindo períodos padrão)
    public function getAvailableTimeSlotsForDate($date)
    {
        if (!$this->active) {
            return [];
        }

        $dayOfWeek = date('w', strtotime($date));
        $bookingType = $this->getBookingTypeForDay($dayOfWeek);

        if (!$bookingType) {
            return []; // Dia não disponível para reservas
        }

        $slots = [];

        if ($bookingType === self::BOOKING_TYPE_PERIOD) {
            // Adicionar slots de períodos padrão
            $periodSlots = $this->getAvailablePeriodSlots($date);
            foreach ($periodSlots as $slot) {
                $slots[] = [
                    'type' => 'period',
                    'period_type' => $slot['type'],
                    'name' => $slot['name'],
                    'start_time' => $date . ' ' . $slot['start_time'],
                    'end_time' => $date . ' ' . $slot['end_time'],
                    'formatted_start' => $slot['start_time'],
                    'formatted_end' => $slot['end_time'],
                    'price' => $slot['price']
                ];
            }
        } else {
            // Adicionar slots por hora
            $hourlySlots = $this->getHourlyTimeSlots($date);
            $slots = array_merge($slots, $hourlySlots);
        }

        return $slots;
    }

    // Obter slots por hora baseados no tempo mínimo de reserva
    private function getHourlyTimeSlots($date)
    {
        $dayOfWeek = date('w', strtotime($date));
        $schedule = $this->availability_schedule;

        if (!$schedule || !isset($schedule[$dayOfWeek]) || !$schedule[$dayOfWeek]['enabled']) {
            return [];
        }

        $daySchedule = $schedule[$dayOfWeek];
        $startTime = $daySchedule['start_time'];
        $endTime = $daySchedule['end_time'];
        
        $slots = [];
        $current = strtotime($date . ' ' . $startTime);
        $end = strtotime($date . ' ' . $endTime);
        $interval = $this->min_booking_hours * 3600; // converter horas para segundos

        while ($current < $end) {
            $slotStart = date('Y-m-d H:i:s', $current);
            $slotEnd = date('Y-m-d H:i:s', $current + $interval);
            
            if ($this->isAvailableAt($slotStart, $slotEnd)) {
                $hours = $this->min_booking_hours;
                $price = $this->price_per_hour ? $this->price_per_hour * $hours : null;
                
                $slots[] = [
                    'type' => 'hourly',
                    'period_type' => 'custom',
                    'name' => "{$hours}h",
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                    'formatted_start' => date('H:i', $current),
                    'formatted_end' => date('H:i', $current + $interval),
                    'price' => $price
                ];
            }
            
            $current += $interval;
        }

        return $slots;
    }

    // Verificar se uma reserva é válida para o tipo de agendamento do dia
    public function validateBookingType($date, $bookingType)
    {
        $dayOfWeek = date('w', strtotime($date));
        $dayBookingType = $this->getBookingTypeForDay($dayOfWeek);

        if (!$dayBookingType) {
            return false; // Dia não disponível
        }

        if ($dayBookingType === self::BOOKING_TYPE_PERIOD) {
            // Para dias de período, apenas reservas por período são válidas
            return in_array($bookingType, ['morning', 'afternoon', 'evening', 'full_day']);
        } else {
            // Para dias por hora, apenas reservas customizadas são válidas
            return $bookingType === 'custom';
        }
    }

    // Verificar limites de reserva para um apartamento
    public function checkBookingLimits($apartmentId, $date = null)
    {
        $limits = [
            'daily' => $this->max_bookings_per_day,
            'monthly' => $this->max_bookings_per_month,
            'yearly' => $this->max_bookings_per_year
        ];

        $results = [];

        foreach ($limits as $period => $limit) {
            if (!$limit) {
                $results[$period] = ['allowed' => true, 'current' => 0, 'limit' => null];
                continue;
            }

            $query = $this->bookings()
                ->where('apartment_id', $apartmentId)
                ->where('status', '!=', 'cancelled');

            switch ($period) {
                case 'daily':
                    $current = $query->whereDate('start_time', $date ?: today())->count();
                    break;
                case 'monthly':
                    $current = $query->whereYear('start_time', date('Y'))
                                   ->whereMonth('start_time', date('m'))->count();
                    break;
                case 'yearly':
                    $current = $query->whereYear('start_time', date('Y'))->count();
                    break;
            }

            $results[$period] = [
                'allowed' => $current < $limit,
                'current' => $current,
                'limit' => $limit,
                'remaining' => $limit - $current
            ];
        }

        return $results;
    }

    // Obter descrição do tipo de reserva para um dia
    public function getBookingTypeDescription($dayOfWeek)
    {
        $schedule = $this->getDefaultBookingSchedule();
        
        if (!isset($schedule[$dayOfWeek]) || !$schedule[$dayOfWeek]['enabled']) {
            return 'Dia não disponível para reservas';
        }

        $type = $schedule[$dayOfWeek]['type'];
        
        if ($type === self::BOOKING_TYPE_HOURLY) {
            return "O morador poderá selecionar de {$this->min_booking_hours} a {$this->max_booking_hours} horas neste dia.";
        } else {
            $periods = $this->getDefaultBookingPeriods();
            $enabledPeriods = [];
            
            foreach ($periods as $periodType => $period) {
                if ($period['enabled']) {
                    $enabledPeriods[] = $period['name'];
                }
            }
            
            if (empty($enabledPeriods)) {
                return 'Nenhum período configurado para este dia';
            }
            
            $periodsText = implode(', ', $enabledPeriods);
            return "O morador poderá escolher os períodos: {$periodsText}";
        }
    }
}
