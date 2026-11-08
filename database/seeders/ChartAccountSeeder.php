<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\ChartAccount;
use App\Models\Condominium;
use Illuminate\Database\Seeder;

class ChartAccountSeeder extends Seeder
{
    public function run(): void
    {
        $condominiums = Condominium::all();

        foreach ($condominiums as $condo) {
            $revenue = ChartAccount::firstOrCreate([
                'condominium_id' => $condo->id,
                'name' => 'Receitas',
                'type' => AccountType::RECEITA,
                'parent_id' => null,
            ]);

            $expense = ChartAccount::firstOrCreate([
                'condominium_id' => $condo->id,
                'name' => 'Despesas',
                'type' => AccountType::DESPESA,
                'parent_id' => null,
            ]);

            ChartAccount::factory()->count(3)->create([
                'condominium_id' => $condo->id,
                'parent_id' => $revenue->id,
                'type' => AccountType::RECEITA,
            ]);

            ChartAccount::factory()->count(3)->create([
                'condominium_id' => $condo->id,
                'parent_id' => $expense->id,
                'type' => AccountType::DESPESA,
            ]);
        }

        $this->command->info('Plano de contas inicial criado.');
    }
}
