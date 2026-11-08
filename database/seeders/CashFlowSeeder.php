<?php

namespace Database\Seeders;

use App\Models\CashFlow;
use App\Models\Condominium;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class CashFlowSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Condominium::all() as $condo) {
            $transactions = Transaction::where('condominium_id', $condo->id)->get();
            foreach ($transactions as $transaction) {
                CashFlow::factory()->create([
                    'condominium_id' => $condo->id,
                    'transaction_id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'date' => $transaction->date,
                ]);
            }
        }

        $this->command->info('Fluxo de caixa gerado.');
    }
}
