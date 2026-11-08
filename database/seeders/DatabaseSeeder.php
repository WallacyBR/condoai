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
            // 1. Criar módulos antes dos perfis para permitir vincular permissões
            ModuleSeeder::class,

            // 2. Criar perfis e associar módulos/permissões
            DefaultProfileSeeder::class,

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

            // 10. Novas tabelas financeiras
            BankSeeder::class,
            PayableSeeder::class,
            ReceivableSeeder::class,
            ExpenseRequestSeeder::class,
            CashFlowSeeder::class,

            // 11. Imagens demonstrativas geradas para registros de seed
            DemoImageSeeder::class,
        ]);
    }
}
