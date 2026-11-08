<?php

namespace Database\Seeders;

use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $condominiums = Condominium::all();
        $users = User::all();

        foreach ($condominiums as $condo) {
            $accounts = ChartAccount::where('condominium_id', $condo->id)->get();
            if ($accounts->isEmpty()) {
                continue;
            }

            $user = $users->where('condominium_id', $condo->id)->first() ?? $users->first();

            Transaction::factory()->count(10)->create([
                'condominium_id' => $condo->id,
                'created_by' => $user->id,
            ])->each(function ($transaction) use ($accounts) {
                $transaction->update([
                    'chart_account_id' => $accounts->random()->id,
                ]);
            });
        }

        $this->command->info('Transações de teste criadas.');
    }
}
