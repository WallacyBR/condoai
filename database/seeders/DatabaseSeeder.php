<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ordem correta de execução respeitando dependências
        $this->call([
            // 1. Criar perfis primeiro (não dependem de nada)
            DefaultProfileSeeder::class,
            
            // 2. Criar módulos (não dependem de nada)
            ModuleSeeder::class,
            
            // 3. Criar condomínios, usuários e estrutura básica
            TestUserSeeder::class,
            CustomCondominiumResidentsSeeder::class,
            
            // 4. Criar dados complementares que dependem da estrutura básica
            MaintenanceAreasSeeder::class,
            ResidentClassificationSeeder::class,
            
            // 5. Criar dados que dependem de usuários e condomínios
            NoticeSeeder::class,
            OccurrenceSeeder::class,
            ProtectiveMeasureSeeder::class,
            
            // 6. Criar dados de infraestrutura
            CommonAreaSeeder::class,
            ParkingSpotSeeder::class,
            MaintenanceEquipmentSeeder::class,
            
            // 7. Criar dados pessoais dos moradores
            VehiclePetSeeder::class,
            
            // 8. Criar dados de atividades
            AdSeeder::class,
            PackageSeeder::class,
            VisitorSeeder::class,
            CommonAreaBookingSeeder::class,

            // 9. Dados financeiros iniciais
            ChartAccountSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
