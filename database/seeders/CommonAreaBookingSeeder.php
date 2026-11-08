<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommonAreaBooking;
use App\Models\CommonArea;
use App\Models\Resident;
use App\Models\Condominium;

class CommonAreaBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $condominiums = Condominium::all();
        
        if ($condominiums->isEmpty()) {
            $this->command->warn('Nenhum condomínio encontrado. Execute primeiro o CustomCondominiumResidentsSeeder.');
            return;
        }

        $commonAreas = CommonArea::all();
        
        if ($commonAreas->isEmpty()) {
            $this->command->warn('Nenhuma área comum encontrada. Execute primeiro o CommonAreaSeeder.');
            return;
        }

        $residents = Resident::all();
        
        if ($residents->isEmpty()) {
            $this->command->warn('Nenhum morador encontrado. Execute primeiro o CustomCondominiumResidentsSeeder.');
            return;
        }

        foreach ($condominiums as $condominium) {
            $condoAreas = $commonAreas->where('condominium_id', $condominium->id);
            $condoResidents = $residents->where('condominium_id', $condominium->id);
            
            if ($condoAreas->isEmpty() || $condoResidents->isEmpty()) {
                continue;
            }

            // Criar reservas para os próximos 60 dias e últimos 30 dias
            for ($i = 0; $i < 20; $i++) {
                $area = $condoAreas->random();
                $resident = $condoResidents->random();
                
                // Definir data da reserva (pode ser passada, presente ou futura)
                $bookingDate = now()->addDays(rand(-30, 60));
                $startTime = $bookingDate->copy()->setHours(rand(8, 18))->setMinutes([0, 30][rand(0, 1)]);
                $duration = [2, 3, 4, 6, 8][rand(0, 4)]; // duração em horas
                $endTime = $startTime->copy()->addHours($duration);
                
                $status = $this->getBookingStatus($bookingDate);
                
                CommonAreaBooking::create([
                    'booking_date' => $bookingDate->format('Y-m-d'),
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                    'duration_hours' => $duration,
                    'purpose' => $this->getRandomPurpose($area->name),
                    'guest_count' => rand(5, 80),
                    'total_amount' => $area->price_per_hour * $duration,
                    'payment_status' => $status === 'confirmada' ? 'pago' : 'pendente',
                    'status' => $status,
                    'special_requirements' => $i % 5 === 0 ? 'Mesa extra para buffet' : null,
                    'cleaning_fee' => $area->name === 'Salão de Festas' ? 50.00 : 0,
                    'deposit_amount' => $area->requires_authorization ? 200.00 : 0,
                    'booking_rules_accepted' => true,
                    'confirmation_date' => $status === 'confirmada' ? $startTime->copy()->subDays(rand(1, 7)) : null,
                    'cancellation_reason' => $status === 'cancelada' ? 'Imprevisto familiar' : null,
                    'cancelled_at' => $status === 'cancelada' ? $startTime->copy()->subDays(rand(1, 3)) : null,
                    'resident_id' => $resident->id,
                    'common_area_id' => $area->id,
                    'condominium_id' => $condominium->id,
                    'created_at' => $startTime->copy()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Reservas de áreas comuns criadas com sucesso para ' . $condominiums->count() . ' condomínio(s)!');
    }

    private function getBookingStatus(\Carbon\Carbon $bookingDate): string
    {
        $now = now();
        
        if ($bookingDate->isPast()) {
            // Para datas passadas, 90% são realizadas, 10% canceladas
            return rand(1, 10) <= 9 ? 'realizada' : 'cancelada';
        } elseif ($bookingDate->isToday() || $bookingDate->isTomorrow()) {
            // Para hoje/amanhã, 95% confirmadas
            return rand(1, 100) <= 95 ? 'confirmada' : 'pendente';
        } else {
            // Para datas futuras, mix de confirmadas e pendentes
            return rand(1, 100) <= 70 ? 'confirmada' : 'pendente';
        }
    }

    private function getRandomPurpose(string $areaName): string
    {
        $purposes = [
            'Salão de Festas' => [
                'Aniversário de 15 anos', 'Festa de casamento', 'Aniversário adulto', 
                'Confraternização familiar', 'Festa de formatura', 'Chá de bebê'
            ],
            'Churrasqueira' => [
                'Churrasco familiar', 'Confraternização amigos', 'Almoço de domingo',
                'Reunião familiar', 'Aniversário informal'
            ],
            'Quadra Poliesportiva' => [
                'Torneio de futebol', 'Aula de educação física', 'Treino de vôlei',
                'Competição entre vizinhos', 'Atividade recreativa'
            ],
            'Academia' => [
                'Treino personalizado', 'Aula particular', 'Sessão de fisioterapia',
                'Treino em grupo', 'Atividade física'
            ],
        ];

        if (isset($purposes[$areaName])) {
            return $purposes[$areaName][array_rand($purposes[$areaName])];
        }

        return 'Evento social';
    }
} 