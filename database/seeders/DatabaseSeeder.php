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
        $this->call([
            DefaultProfileSeeder::class,
            ModuleSeeder::class,
            TestUserSeeder::class,
        ]);

        // Chamar o novo seeder customizado para os condomínios e moradores
        $this->call([
            CustomCondominiumResidentsSeeder::class,
            OccurrenceSeeder::class, // Adicionar o novo seeder aqui
        ]);
    }
}
