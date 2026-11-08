<?php

namespace Database\Seeders;

use App\Models\AccessRequest;
use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\Resident;
use App\Models\Visitor;
use App\Models\VisitorVehicle;
use Illuminate\Database\Seeder;

class VisitorSeeder extends Seeder
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

        $residents = Resident::all();

        if ($residents->isEmpty()) {
            $this->command->warn('Nenhum morador encontrado. Execute primeiro o CustomCondominiumResidentsSeeder.');

            return;
        }

        $visitorNames = [
            'Ana Silva', 'João Santos', 'Maria Oliveira', 'Carlos Pereira', 'Fernanda Costa',
            'Pedro Rodrigues', 'Juliana Lima', 'Rafael Almeida', 'Camila Ferreira', 'Lucas Mendes',
            'Beatriz Gomes', 'Gabriel Martins', 'Larissa Barbosa', 'Felipe Ribeiro', 'Amanda Carvalho',
        ];

        $visitorTypes = ['Visitante', 'Prestador de Serviço', 'Delivery', 'Familiar'];
        $serviceTypes = ['Técnico', 'Entregador', 'Manutenção', 'Limpeza', 'Segurança'];

        foreach ($condominiums as $condominium) {
            $condoResidents = $residents->where('condominium_id', $condominium->id);
            $apartments = Apartment::where('condominium_id', $condominium->id)->get();

            if ($condoResidents->isEmpty() || $apartments->isEmpty()) {
                continue;
            }

            // Criar visitantes e solicitações dos últimos 60 dias
            for ($i = 0; $i < 30; $i++) {
                $resident = $condoResidents->random();
                $apartment = $apartments->random();
                $visitorName = $visitorNames[array_rand($visitorNames)];
                $visitorType = $visitorTypes[array_rand($visitorTypes)];

                // Criar visitante
                $visitor = Visitor::create([
                    'name' => $visitorName,
                    'document' => $this->generateCPF(),
                    'phone' => '(11) '.rand(90000, 99999).'-'.rand(1000, 9999),
                    'email' => strtolower(str_replace(' ', '.', $visitorName)).'@email.com',
                    'visitor_type' => $visitorType,
                    'company' => $visitorType === 'Prestador de Serviço' ? $serviceTypes[array_rand($serviceTypes)].' Ltda' : null,
                    'condominium_id' => $condominium->id,
                ]);

                // 50% dos visitantes têm veículo
                if (rand(0, 1)) {
                    VisitorVehicle::create([
                        'brand' => ['Toyota', 'Honda', 'Fiat', 'Chevrolet'][rand(0, 3)],
                        'model' => ['Corolla', 'Civic', 'Uno', 'Onix'][rand(0, 3)],
                        'color' => ['Branco', 'Preto', 'Prata', 'Azul'][rand(0, 3)],
                        'license_plate' => $this->generateLicensePlate(),
                        'visitor_id' => $visitor->id,
                        'condominium_id' => $condominium->id,
                    ]);
                }

                // Criar solicitação de acesso
                $requestDate = now()->subDays(rand(0, 60));
                $status = ['pendente', 'aprovada', 'rejeitada', 'expirada'][rand(0, 3)];
                $visitDate = $requestDate->copy()->addDays(rand(0, 7));

                AccessRequest::create([
                    'visitor_name' => $visitor->name,
                    'visitor_document' => $visitor->document,
                    'visitor_phone' => $visitor->phone,
                    'visit_date' => $visitDate,
                    'visit_time' => $visitDate->copy()->addHours(rand(8, 20))->format('H:i:s'),
                    'duration_hours' => rand(1, 8),
                    'purpose' => $this->getPurpose($visitorType),
                    'status' => $status,
                    'observations' => $i % 4 === 0 ? 'Visitante frequente' : null,
                    'authorized_by' => $status === 'aprovada' ? $resident->name : null,
                    'authorized_at' => $status === 'aprovada' ? $requestDate->copy()->addMinutes(rand(10, 120)) : null,
                    'rejection_reason' => $status === 'rejeitada' ? 'Horário não permitido' : null,
                    'entry_time' => $status === 'aprovada' && rand(0, 1) ? $visitDate->copy()->addMinutes(rand(0, 60)) : null,
                    'exit_time' => null, // Será preenchido quando o visitante sair
                    'resident_id' => $resident->id,
                    'apartment_id' => $apartment->id,
                    'visitor_id' => $visitor->id,
                    'condominium_id' => $condominium->id,
                    'created_at' => $requestDate,
                    'updated_at' => $requestDate,
                ]);
            }
        }

        $this->command->info('Visitantes e solicitações de acesso criados com sucesso para '.$condominiums->count().' condomínio(s)!');
    }

    private function generateCPF(): string
    {
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= rand(0, 9);
        }

        // Calcular dígitos verificadores (simplificado)
        $cpf .= rand(0, 9).rand(0, 9);

        return substr($cpf, 0, 3).'.'.substr($cpf, 3, 3).'.'.substr($cpf, 6, 3).'-'.substr($cpf, 9, 2);
    }

    private function generateLicensePlate(): string
    {
        $letters = chr(rand(65, 90)).chr(rand(65, 90)).chr(rand(65, 90));
        $numbers = rand(1000, 9999);

        return $letters.'-'.$numbers;
    }

    private function getPurpose(string $visitorType): string
    {
        return match ($visitorType) {
            'Visitante' => 'Visita social',
            'Prestador de Serviço' => 'Prestação de serviços',
            'Delivery' => 'Entrega de produto',
            'Familiar' => 'Visita familiar',
            default => 'Outros',
        };
    }
}
