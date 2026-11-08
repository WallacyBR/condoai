<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Payable;
use Illuminate\Database\Seeder;

class PayableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Condominium::all() as $condo) {
            $account = ChartAccount::where('condominium_id', $condo->id)->first();
            $bank = Bank::where('condominium_id', $condo->id)->first();
            if (! $account || ! $bank) {
                continue;
            }
            Payable::factory()->count(5)->create([
                'condominium_id' => $condo->id,
                'chart_account_id' => $account->id,
                'bank_id' => $bank->id,
            ]);
        }

        $this->command->info('Contas a pagar geradas.');
    }
}
