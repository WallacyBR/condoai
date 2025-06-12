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
            TestUserSeeder::class,
        ]);

        \App\Models\Condominium::factory()
            ->count(3)
            ->has(\App\Models\Resident::factory()->count(5), 'residents')
            ->has(
                \App\Models\Tower::factory()
                    ->count(2)
                    ->has(
                        \App\Models\Block::factory()
                            ->count(2)
                            ->state(function (array $attributes, \App\Models\Tower $tower) {
                                return ['condominium_id' => $tower->condominium_id];
                            })
                            ->has(\App\Models\Apartment::factory()->count(3), 'apartments'),
                        'blocks'
                    ),
                'towers'
            )
            ->create();
    }
}
