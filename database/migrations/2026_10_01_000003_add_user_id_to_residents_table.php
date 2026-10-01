<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STAFF_PROFILE_NAMES = ['SysOp', 'Administradora', 'Síndico', 'Zelador'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('condominium_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->unique(['condominium_id', 'user_id'], 'residents_condominium_user_unique');
        });

        $this->backfillUserLinksByEmail();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropUnique('residents_condominium_user_unique');
            $table->dropConstrainedForeignId('user_id');
        });
    }

    /**
     * Backfill one-shot: associa user_id quando há match 1:1 por e-mail no mesmo condomínio
     * com membership ativa. E-mail não é SoT em runtime após esta migration.
     */
    private function backfillUserLinksByEmail(): void
    {
        $residents = DB::table('residents')
            ->whereNull('user_id')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get(['id', 'condominium_id', 'email']);

        foreach ($residents as $resident) {
            $normalizedEmail = strtolower(trim($resident->email));

            $candidateUsers = DB::table('users')
                ->join('condominium_user', function ($join) use ($resident) {
                    $join->on('users.id', '=', 'condominium_user.user_id')
                        ->where('condominium_user.condominium_id', '=', $resident->condominium_id);
                })
                ->leftJoin('profiles', 'users.profile_id', '=', 'profiles.id')
                ->whereRaw('LOWER(TRIM(users.email)) = ?', [$normalizedEmail])
                ->select('users.id', 'profiles.name as profile_name')
                ->get();

            if ($candidateUsers->count() !== 1) {
                Log::info('Backfill residents.user_id: skip por ambiguidade ou ausência de match', [
                    'resident_id' => $resident->id,
                    'condominium_id' => $resident->condominium_id,
                    'email' => $resident->email,
                    'candidate_count' => $candidateUsers->count(),
                ]);

                continue;
            }

            $user = $candidateUsers->first();

            if (in_array($user->profile_name, self::STAFF_PROFILE_NAMES, true)) {
                Log::info('Backfill residents.user_id: skip por perfil staff', [
                    'resident_id' => $resident->id,
                    'user_id' => $user->id,
                    'profile' => $user->profile_name,
                ]);

                continue;
            }

            $existingLink = DB::table('residents')
                ->where('condominium_id', $resident->condominium_id)
                ->where('user_id', $user->id)
                ->where('id', '!=', $resident->id)
                ->exists();

            if ($existingLink) {
                Log::info('Backfill residents.user_id: skip por user já vinculado no condomínio', [
                    'resident_id' => $resident->id,
                    'user_id' => $user->id,
                    'condominium_id' => $resident->condominium_id,
                ]);

                continue;
            }

            DB::table('residents')
                ->where('id', $resident->id)
                ->update(['user_id' => $user->id]);

            Log::info('Backfill residents.user_id: vínculo criado', [
                'resident_id' => $resident->id,
                'user_id' => $user->id,
                'condominium_id' => $resident->condominium_id,
            ]);
        }
    }
};
