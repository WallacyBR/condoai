<?php

namespace Database\Seeders;

use App\Models\Condominium;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceEquipment;
use Illuminate\Database\Seeder;

class MaintenanceEquipmentSeeder extends Seeder
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

        $maintenanceAreas = MaintenanceArea::all();

        if ($maintenanceAreas->isEmpty()) {
            $this->command->warn('Nenhuma área de manutenção encontrada. Execute primeiro o MaintenanceAreasSeeder.');

            return;
        }

        $equipmentsByArea = [
            'Elevadores' => [
                ['name' => 'Elevador Social 01', 'description' => 'Elevador social para passageiros - Atlas Schindler 3300 (EL-001-2020)'],
                ['name' => 'Elevador Social 02', 'description' => 'Elevador social para passageiros - Atlas Schindler 3300 (EL-002-2020)'],
                ['name' => 'Elevador de Serviço', 'description' => 'Elevador de serviço - Thyssenkrupp Evolution 100 (EL-003-2019)'],
            ],
            'Piscina' => [
                ['name' => 'Bomba de Recirculação', 'description' => 'Bomba para recirculação da água - Dancor PF-22 (BC-001-2021)'],
                ['name' => 'Sistema de Filtragem', 'description' => 'Sistema de filtragem da piscina - Pentair FNS Plus 60 (SF-001-2021)'],
                ['name' => 'Aquecedor Solar', 'description' => 'Aquecedor solar para piscina - Soletrol Pro 600L (AS-001-2020)'],
                ['name' => 'Motor do Aspirador', 'description' => 'Motor do aspirador automático - Sodramar Vac 7 Plus (MA-001-2022)'],
            ],
            'Jardim' => [
                ['name' => 'Sistema de Irrigação Zona A', 'description' => 'Sistema automático de irrigação - Rain Bird ESP-Me (SI-001-2021)'],
                ['name' => 'Sistema de Irrigação Zona B', 'description' => 'Sistema automático de irrigação - Rain Bird ESP-Me (SI-002-2021)'],
                ['name' => 'Bomba de Irrigação', 'description' => 'Bomba para sistema de irrigação - KSB Multi Eco 35 (BI-001-2020)'],
            ],
            'Portaria' => [
                ['name' => 'Cancela Automática', 'description' => 'Cancela automática de entrada - Peccinin Fast Gatter (CA-001-2019)'],
                ['name' => 'Portão Social', 'description' => 'Portão social automatizado - Garen Automatizador Deslizante (PS-001-2020)'],
                ['name' => 'Sistema CFTV', 'description' => 'Sistema de monitoramento - Hikvision DS-7616NI-K2 (CFTV-001-2021)'],
                ['name' => 'Interfone Central', 'description' => 'Central de interfone - Amelco AM-CPEX-16 (IF-001-2020)'],
            ],
            'Salão de Festas' => [
                ['name' => 'Ar Condicionado Split 1', 'description' => 'Ar condicionado split - Carrier X-Power 60000 BTUs (AC-001-2020)'],
                ['name' => 'Ar Condicionado Split 2', 'description' => 'Ar condicionado split - Carrier X-Power 60000 BTUs (AC-002-2020)'],
                ['name' => 'Som Ambiente', 'description' => 'Sistema de som ambiente - JBL Control 26CT (SA-001-2019)'],
            ],
            'Garagem' => [
                ['name' => 'Portão Basculante A', 'description' => 'Portão basculante automático - Peccinin Potenza Quattro (PB-001-2020)'],
                ['name' => 'Portão Basculante B', 'description' => 'Portão basculante automático - Peccinin Potenza Quattro (PB-002-2020)'],
                ['name' => 'Sistema de Ventilação', 'description' => 'Exaustor industrial - Ventisilva Exaustor Industrial (SV-001-2019)'],
            ],
            'Playground' => [
                ['name' => 'Brinquedo Escorregador', 'description' => 'Escorregador infantil - Playground Brasil PB-ESC-01 (BE-001-2021)'],
                ['name' => 'Brinquedo Gangorra', 'description' => 'Gangorra dupla infantil - Playground Brasil PB-GAN-02 (BG-001-2021)'],
                ['name' => 'Piso Emborrachado', 'description' => 'Piso de segurança - Pisosafe Infantil 20mm (PE-001-2021)'],
            ],
            'Quadra de Esportes' => [
                ['name' => 'Iluminação LED', 'description' => 'Sistema de iluminação - Philips ArenaVision LED gen2 (IL-001-2020)'],
                ['name' => 'Tela de Proteção', 'description' => 'Tela de proteção lateral - Sombrite Monofilamento 4m (TP-001-2019)'],
            ],
        ];

        foreach ($condominiums as $condominium) {
            $condoAreas = $maintenanceAreas->where('condominium_id', $condominium->id);

            foreach ($condoAreas as $area) {
                if (isset($equipmentsByArea[$area->name])) {
                    foreach ($equipmentsByArea[$area->name] as $equipment) {
                        MaintenanceEquipment::firstOrCreate(
                            [
                                'name' => $equipment['name'],
                                'maintenance_area_id' => $area->id,
                                'condominium_id' => $condominium->id,
                            ],
                            [
                                'description' => $equipment['description'],
                                'location_details' => 'Localizado na área: '.$area->name,
                                'qr_code_hash' => bin2hex(random_bytes(16)), // Gerar hash único para QR Code
                                'qr_code_access_level' => in_array($area->name, ['Elevadores', 'Portaria']) ? 'specific_users' : 'internal_users',
                                'maintenance_area_id' => $area->id,
                                'condominium_id' => $condominium->id,
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('Equipamentos de manutenção criados com sucesso para '.$condominiums->count().' condomínio(s)!');
    }
}
