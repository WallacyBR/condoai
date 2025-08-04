<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CommonArea;
use App\Models\CommonAreaBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CommonAreaController extends Controller
{
    public function index(Request $request)
    {
        $query = CommonArea::query();

        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return $query->with('bookings')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $condoId = $request->condominium_id;
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('common_areas')->where(fn ($q) => $q->where('condominium_id', $condoId)),
            ],
            'description' => 'nullable|string',
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(fn ($q) => $q->where('active', true)),
            ],
            'capacity' => 'required|integer|min:1',
            'main_photo' => 'nullable|string', // Base64 encoded image
            'additional_photos' => 'nullable|array',
            'additional_photos.*' => 'string', // Base64 encoded images
            'active' => 'boolean',
            'availability_schedule' => 'nullable|array',
            'unavailability_periods' => 'nullable|array',
            'price_per_hour' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
            'min_booking_hours' => 'nullable|integer|min:1|max:24',
            'max_booking_hours' => 'nullable|integer|min:1|max:24',
            'requires_approval' => 'boolean',
            'booking_periods' => 'nullable|array',
            'booking_periods.morning.enabled' => 'boolean',
            'booking_periods.morning.start_time' => 'required_if:booking_periods.morning.enabled,true|date_format:H:i',
            'booking_periods.morning.end_time' => 'required_if:booking_periods.morning.enabled,true|date_format:H:i|after:booking_periods.morning.start_time',
            'booking_periods.morning.price' => 'nullable|numeric|min:0',
            'booking_periods.afternoon.enabled' => 'boolean',
            'booking_periods.afternoon.start_time' => 'required_if:booking_periods.afternoon.enabled,true|date_format:H:i',
            'booking_periods.afternoon.end_time' => 'required_if:booking_periods.afternoon.enabled,true|date_format:H:i|after:booking_periods.afternoon.start_time',
            'booking_periods.afternoon.price' => 'nullable|numeric|min:0',
            'booking_periods.evening.enabled' => 'boolean',
            'booking_periods.evening.start_time' => 'required_if:booking_periods.evening.enabled,true|date_format:H:i',
            'booking_periods.evening.end_time' => 'required_if:booking_periods.evening.enabled,true|date_format:H:i|after:booking_periods.evening.start_time',
            'booking_periods.evening.price' => 'nullable|numeric|min:0',
            'booking_periods.full_day.enabled' => 'boolean',
            'booking_periods.full_day.start_time' => 'required_if:booking_periods.full_day.enabled,true|date_format:H:i',
            'booking_periods.full_day.end_time' => 'required_if:booking_periods.full_day.enabled,true|date_format:H:i|after:booking_periods.full_day.start_time',
            'booking_periods.full_day.price' => 'nullable|numeric|min:0',
            'allow_custom_periods' => 'boolean',
            'booking_schedule' => 'nullable|array',
            'booking_schedule.*.type' => 'required|in:hourly,period',
            'booking_schedule.*.enabled' => 'boolean',
            'max_bookings_per_day' => 'nullable|integer|min:1',
            'max_bookings_per_month' => 'nullable|integer|min:1',
            'max_bookings_per_year' => 'nullable|integer|min:1',
        ]);

        // Processar imagem principal
        if ($request->has('main_photo') && $request->main_photo) {
            $validated['main_photo'] = $this->processImage($request->main_photo, 'common-areas/main');
        }

        // Processar fotos adicionais
        if ($request->has('additional_photos') && $request->additional_photos) {
            $additionalPhotos = [];
            foreach ($request->additional_photos as $photo) {
                $additionalPhotos[] = $this->processImage($photo, 'common-areas/additional');
            }
            $validated['additional_photos'] = $additionalPhotos;
        }

        // Calcular preços automáticos para períodos se não fornecidos
        if (isset($validated['booking_periods']) && $validated['price_per_hour']) {
            $validated['booking_periods'] = $this->calculatePeriodPrices($validated['booking_periods'], $validated['price_per_hour']);
        }

        $commonArea = CommonArea::create($validated);

        return response()->json($commonArea->load('condominium'), 201);
    }

    public function show(CommonArea $commonArea)
    {
        return $commonArea->load(['bookings', 'condominium']);
    }

    public function update(Request $request, CommonArea $commonArea)
    {
        $condoId = $commonArea->condominium_id;
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('common_areas')->where(fn ($q) => $q->where('condominium_id', $condoId))->ignore($commonArea->id),
            ],
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'main_photo' => 'nullable|string', // Base64 encoded image
            'additional_photos' => 'nullable|array',
            'additional_photos.*' => 'string', // Base64 encoded images
            'active' => 'boolean',
            'availability_schedule' => 'nullable|array',
            'unavailability_periods' => 'nullable|array',
            'price_per_hour' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
            'min_booking_hours' => 'nullable|integer|min:1|max:24',
            'max_booking_hours' => 'nullable|integer|min:1|max:24',
            'requires_approval' => 'boolean',
            'booking_periods' => 'nullable|array',
            'booking_periods.morning.enabled' => 'boolean',
            'booking_periods.morning.start_time' => 'required_if:booking_periods.morning.enabled,true|date_format:H:i',
            'booking_periods.morning.end_time' => 'required_if:booking_periods.morning.enabled,true|date_format:H:i|after:booking_periods.morning.start_time',
            'booking_periods.morning.price' => 'nullable|numeric|min:0',
            'booking_periods.afternoon.enabled' => 'boolean',
            'booking_periods.afternoon.start_time' => 'required_if:booking_periods.afternoon.enabled,true|date_format:H:i',
            'booking_periods.afternoon.end_time' => 'required_if:booking_periods.afternoon.enabled,true|date_format:H:i|after:booking_periods.afternoon.start_time',
            'booking_periods.afternoon.price' => 'nullable|numeric|min:0',
            'booking_periods.evening.enabled' => 'boolean',
            'booking_periods.evening.start_time' => 'required_if:booking_periods.evening.enabled,true|date_format:H:i',
            'booking_periods.evening.end_time' => 'required_if:booking_periods.evening.enabled,true|date_format:H:i|after:booking_periods.evening.start_time',
            'booking_periods.evening.price' => 'nullable|numeric|min:0',
            'booking_periods.full_day.enabled' => 'boolean',
            'booking_periods.full_day.start_time' => 'required_if:booking_periods.full_day.enabled,true|date_format:H:i',
            'booking_periods.full_day.end_time' => 'required_if:booking_periods.full_day.enabled,true|date_format:H:i|after:booking_periods.full_day.start_time',
            'booking_periods.full_day.price' => 'nullable|numeric|min:0',
            'allow_custom_periods' => 'boolean',
            'booking_schedule' => 'nullable|array',
            'booking_schedule.*.type' => 'required|in:hourly,period',
            'booking_schedule.*.enabled' => 'boolean',
        ]);

        // Processar imagem principal
        if ($request->has('main_photo')) {
            // Remover foto anterior
            if ($commonArea->main_photo) {
                Storage::disk('public')->delete($commonArea->main_photo);
            }
            
            if ($request->main_photo) {
                $validated['main_photo'] = $this->processImage($request->main_photo, 'common-areas/main');
            } else {
                $validated['main_photo'] = null;
            }
        }

        // Processar fotos adicionais
        if ($request->has('additional_photos')) {
            // Remover fotos anteriores
            if ($commonArea->additional_photos) {
                foreach ($commonArea->additional_photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            
            if ($request->additional_photos) {
                $additionalPhotos = [];
                foreach ($request->additional_photos as $photo) {
                    $additionalPhotos[] = $this->processImage($photo, 'common-areas/additional');
                }
                $validated['additional_photos'] = $additionalPhotos;
            } else {
                $validated['additional_photos'] = null;
            }
        }

        // Calcular preços automáticos para períodos se não fornecidos
        if (isset($validated['booking_periods']) && $validated['price_per_hour']) {
            $validated['booking_periods'] = $this->calculatePeriodPrices($validated['booking_periods'], $validated['price_per_hour']);
        }

        $commonArea->update($validated);

        return $commonArea->load('condominium');
    }

    public function destroy(CommonArea $commonArea)
    {
        // Remover fotos do storage
        if ($commonArea->main_photo) {
            Storage::disk('public')->delete($commonArea->main_photo);
        }
        
        if ($commonArea->additional_photos) {
            foreach ($commonArea->additional_photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $commonArea->delete();

        return response()->json(null, 204);
    }

    public function bookings(CommonArea $commonArea)
    {
        return $commonArea->bookings()
            ->with(['apartment.tower', 'apartment.block'])
            ->orderBy('start_time')
            ->get();
    }

    public function allBookings(Request $request)
    {
        $condominiumId = $request->get('condominium_id');
        
        if (!$condominiumId) {
            return response()->json(['message' => 'Condominium ID is required'], 400);
        }

        return \App\Models\CommonAreaBooking::with([
            'commonArea', 
            'apartment.tower', 
            'apartment.block'
        ])
        ->whereHas('commonArea', function ($query) use ($condominiumId) {
            $query->where('condominium_id', $condominiumId);
        })
        ->orderBy('start_time', 'desc')
        ->get();
    }

    public function showBooking($id)
    {
        $booking = \App\Models\CommonAreaBooking::with([
            'commonArea', 
            'apartment.tower', 
            'apartment.block'
        ])->find($id);

        if (!$booking) {
            return response()->json(['message' => 'Reserva não encontrada'], 404);
        }

        return response()->json($booking);
    }

    public function book(Request $request, CommonArea $commonArea)
    {
        $validated = $request->validate([
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')->where(fn ($q) => $q->where('condominium_id', $commonArea->condominium_id)),
            ],
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'booking_type' => 'nullable|in:custom,morning,afternoon,evening,full_day',
            'notes' => 'nullable|string|max:500',
        ]);

        // Verificar se a área está ativa
        if (!$commonArea->active) {
            return response()->json(['message' => 'Esta área comum não está disponível para reservas.'], 422);
        }

        // Verificar limites de reserva
        $bookingLimits = $commonArea->checkBookingLimits($validated['apartment_id'], date('Y-m-d', strtotime($validated['start_time'])));
        
        $limitErrors = [];
        foreach ($bookingLimits as $period => $limit) {
            if (!$limit['allowed']) {
                $periodNames = [
                    'daily' => 'diário',
                    'monthly' => 'mensal',
                    'yearly' => 'anual'
                ];
                $limitErrors[] = "Limite {$periodNames[$period]} atingido ({$limit['current']}/{$limit['limit']})";
            }
        }
        
        if (!empty($limitErrors)) {
            return response()->json(['message' => 'Limites de reserva excedidos: ' . implode(', ', $limitErrors)], 422);
        }

        // Determinar tipo de reserva se não fornecido
        if (!isset($validated['booking_type'])) {
            $validated['booking_type'] = 'custom';
        }

        // Verificar se o tipo de reserva é válido para o dia
        $date = date('Y-m-d', strtotime($validated['start_time']));
        if (!$commonArea->validateBookingType($date, $validated['booking_type'])) {
            $dayOfWeek = date('w', strtotime($date));
            $dayName = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'][$dayOfWeek];
            $bookingType = $commonArea->getBookingTypeForDay($dayOfWeek);
            
            if (!$bookingType) {
                return response()->json(['message' => "Este dia ({$dayName}) não está disponível para reservas."], 422);
            }
            
            $typeName = $bookingType === 'period' ? 'períodos padrão' : 'por hora';
            return response()->json(['message' => "Neste dia ({$dayName}) apenas reservas {$typeName} são permitidas."], 422);
        }

        // Se for reserva por período padrão, calcular horários automaticamente
        if ($validated['booking_type'] !== 'custom') {
            $periods = $commonArea->getDefaultBookingPeriods();
            $periodType = $validated['booking_type'];
            
            if (!isset($periods[$periodType]) || !$periods[$periodType]['enabled']) {
                return response()->json(['message' => 'Este período não está disponível para reserva.'], 422);
            }

            $period = $periods[$periodType];
            $validated['start_time'] = $date . ' ' . $period['start_time'];
            $validated['end_time'] = $date . ' ' . $period['end_time'];
        }

        // Verificar limites de tempo de reserva para reservas customizadas
        if ($validated['booking_type'] === 'custom') {
            $startTime = new \DateTime($validated['start_time']);
            $endTime = new \DateTime($validated['end_time']);
            $duration = $endTime->diff($startTime)->h + ($endTime->diff($startTime)->i / 60);

            if ($duration < $commonArea->min_booking_hours) {
                return response()->json([
                    'message' => "O tempo mínimo de reserva é de {$commonArea->min_booking_hours} hora(s)."
                ], 422);
            }

            if ($duration > $commonArea->max_booking_hours) {
                return response()->json([
                    'message' => "O tempo máximo de reserva é de {$commonArea->max_booking_hours} hora(s)."
                ], 422);
            }
        }

        // Verificar disponibilidade usando o método do modelo
        if (!$commonArea->isAvailableAt($validated['start_time'], $validated['end_time'])) {
            return response()->json(['message' => 'Horário indisponível'], 422);
        }

        // Calcular preço total
        $totalPrice = null;
        if ($validated['booking_type'] === 'custom' && $commonArea->price_per_hour) {
            $startTime = new \DateTime($validated['start_time']);
            $endTime = new \DateTime($validated['end_time']);
            $duration = $endTime->diff($startTime)->h + ($endTime->diff($startTime)->i / 60);
            $totalPrice = $commonArea->price_per_hour * $duration;
        } else {
            $totalPrice = $commonArea->calculatePeriodPrice($validated['booking_type']);
        }

        $validated['status'] = $commonArea->requires_approval ? 'pending' : 'confirmed';
        $validated['total_price'] = $totalPrice;
        
        $booking = $commonArea->bookings()->create($validated);

        return response()->json($booking->load(['apartment.tower', 'apartment.block']), 201);
    }

    public function getAvailableSlots(Request $request, CommonArea $commonArea)
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = $validated['date'];
        $dayOfWeek = date('w', strtotime($date));
        $bookingType = $commonArea->getBookingTypeForDay($dayOfWeek);
        
        $slots = $commonArea->getAvailableTimeSlotsForDate($date);

        return response()->json([
            'date' => $date,
            'day_of_week' => $dayOfWeek,
            'day_name' => ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'][$dayOfWeek],
            'booking_type' => $bookingType,
            'available_slots' => $slots,
            'area' => [
                'id' => $commonArea->id,
                'name' => $commonArea->name,
                'min_booking_hours' => $commonArea->min_booking_hours,
                'max_booking_hours' => $commonArea->max_booking_hours,
                'price_per_hour' => $commonArea->price_per_hour,
                'allow_custom_periods' => $commonArea->allow_custom_periods,
            ]
        ]);
    }

    public function cancelBooking(Request $request, CommonArea $commonArea, CommonAreaBooking $booking)
    {
        // Verificar se a reserva pertence à área comum
        if ($booking->common_area_id !== $commonArea->id) {
            return response()->json(['message' => 'Reserva não encontrada'], 404);
        }

        // Verificar se a reserva pode ser cancelada
        if (!$booking->canBeCancelled()) {
            return response()->json(['message' => 'Esta reserva não pode ser cancelada'], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Reserva cancelada com sucesso']);
    }

    private function processImage($base64Image, $directory)
    {
        try {
            // Remover o cabeçalho do base64
            if (strpos($base64Image, 'data:image/') === 0) {
                list($type, $image) = explode(';', $base64Image);
                list(, $image) = explode(',', $image);
            } else {
                $image = $base64Image;
                $type = 'data:image/jpeg'; // Assumir JPEG se não especificado
            }
            
            $imageData = base64_decode($image);

            // Gerar nome de arquivo único
            $extension = str_replace('data:image/', '', $type);
            if (!in_array($extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
                $extension = 'jpg';
            }
            
            $filename = $directory . '/' . Str::uuid() . '.' . $extension;

            // Salvar a imagem
            Storage::disk('public')->put($filename, $imageData);

            return $filename;

        } catch (\Exception $e) {
            \Log::error('Erro ao processar imagem da área comum: ' . $e->getMessage());
            throw new \Exception('Erro ao processar a imagem.');
        }
    }

    // Calcular preços automáticos para períodos
    private function calculatePeriodPrices($bookingPeriods, $pricePerHour)
    {
        foreach ($bookingPeriods as $periodType => &$period) {
            if ($period['enabled'] && !isset($period['price'])) {
                $startTime = \DateTime::createFromFormat('H:i', $period['start_time']);
                $endTime = \DateTime::createFromFormat('H:i', $period['end_time']);
                
                if ($startTime && $endTime) {
                    $hours = ($endTime->getTimestamp() - $startTime->getTimestamp()) / 3600;
                    $period['price'] = $pricePerHour * $hours;
                }
            }
        }

        return $bookingPeriods;
    }
}
