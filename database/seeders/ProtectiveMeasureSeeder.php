<?php

namespace Database\Seeders;

use App\Models\ProtectiveMeasure;
use App\Models\User;
use App\Models\Visitor;
use App\Models\Condominium;
use Illuminate\Database\Seeder;

class ProtectiveMeasureSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('Criando medidas protetivas de exemplo...');

        // Verificar se existem condomínios
        $condominiums = Condominium::all();
        if ($condominiums->isEmpty()) {
            $this->command->warn('Nenhum condomínio encontrado. Execute o CustomCondominiumResidentsSeeder primeiro.');
            return;
        }

        foreach ($condominiums as $condominium) {
            // Buscar moradores e visitantes do condomínio
            $residents = User::where('condominium_id', $condominium->id)->get();
            $visitors = Visitor::where('condominium_id', $condominium->id)->get();

            if ($residents->isEmpty() || $visitors->isEmpty()) {
                $this->command->warn("Condomínio {$condominium->name} não tem moradores ou visitantes suficientes. Pulando...");
                continue;
            }

            // Criar 2-5 medidas protetivas por condomínio
            $numberOfMeasures = rand(2, 5);
            
            for ($i = 0; $i < $numberOfMeasures; $i++) {
                $protectedResident = $residents->random();
                $aggressorVisitor = $visitors->random();

                // Verificar se já existe uma medida entre estes dois
                $existingMeasure = ProtectiveMeasure::where('condominium_id', $condominium->id)
                    ->where('protected_resident_id', $protectedResident->id)
                    ->where('aggressor_visitor_id', $aggressorVisitor->id)
                    ->first();

                if ($existingMeasure) {
                    continue; // Pular se já existe
                }

                ProtectiveMeasure::factory()
                    ->forCondominium($condominium->id)
                    ->between($protectedResident->id, $aggressorVisitor->id)
                    ->create([
                        'is_active' => rand(0, 100) < 80 // 80% de chance de estar ativa
                    ]);
            }

            $this->command->info("Criadas medidas protetivas para o condomínio: {$condominium->name}");
        }

        // Criar algumas medidas específicas com cenários variados
        $this->createSpecificScenarios($condominiums);

        $totalMeasures = ProtectiveMeasure::count();
        $this->command->info("✅ Seeder de medidas protetivas concluído! Total de medidas criadas: {$totalMeasures}");
    }

    /**
     * Criar cenários específicos para demonstração
     */
    private function createSpecificScenarios($condominiums)
    {
        if ($condominiums->isEmpty()) {
            return;
        }

        $firstCondominium = $condominiums->first();
        $residents = User::where('condominium_id', $firstCondominium->id)->get();
        $visitors = Visitor::where('condominium_id', $firstCondominium->id)->get();

        if ($residents->count() < 2 || $visitors->count() < 2) {
            return;
        }

        // Cenário 1: Medida ativa recente
        ProtectiveMeasure::factory()
            ->forCondominium($firstCondominium->id)
            ->between($residents->first()->id, $visitors->first()->id)
            ->recent()
            ->active()
            ->withDocument()
            ->create([
                'process_number' => '0001234-12.2024.8.26.0001',
                'measure_description' => 'Medida protetiva urgente de distanciamento mínimo de 300 metros conforme decisão judicial.',
            ]);

        // Cenário 2: Medida expirada
        if ($residents->count() > 1 && $visitors->count() > 1) {
            ProtectiveMeasure::factory()
                ->forCondominium($firstCondominium->id)
                ->between($residents->skip(1)->first()->id, $visitors->skip(1)->first()->id)
                ->expired()
                ->create([
                    'process_number' => '0005678-34.2023.8.26.0001',
                    'measure_description' => 'Medida protetiva que expirou conforme prazo determinado.',
                ]);
        }

        // Cenário 3: Medida indefinida (sem data fim)
        if ($residents->count() > 2 && $visitors->count() > 2) {
            ProtectiveMeasure::factory()
                ->forCondominium($firstCondominium->id)
                ->between($residents->skip(2)->first()->id, $visitors->skip(2)->first()->id)
                ->indefinite()
                ->active()
                ->create([
                    'process_number' => '0009999-56.2024.8.26.0001',
                    'measure_description' => 'Medida protetiva por prazo indeterminado.',
                ]);
        }

        $this->command->info("✅ Cenários específicos criados para demonstração");
    }
} 