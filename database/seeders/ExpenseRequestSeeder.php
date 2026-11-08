<?php

namespace Database\Seeders;

use App\Models\Condominium;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseRequestSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        foreach (Condominium::all() as $condo) {
            $user = $users->where('condominium_id', $condo->id)->first() ?? $users->first();
            ExpenseRequest::factory()->count(3)->create([
                'condominium_id' => $condo->id,
                'requested_by' => $user->id,
            ]);
        }

        $this->command->info('Solicitações de despesa criadas.');
    }
}
