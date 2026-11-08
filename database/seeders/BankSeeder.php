<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Condominium;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Condominium::all() as $condo) {
            Bank::factory()->count(2)->create(['condominium_id' => $condo->id]);
        }

        $this->command->info('Bancos de exemplo criados.');
    }
}
