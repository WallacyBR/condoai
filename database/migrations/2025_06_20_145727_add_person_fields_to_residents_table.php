<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            // Primeiro, renomear a coluna phone para cell_phone se ela existir
            if (Schema::hasColumn('residents', 'phone') && !Schema::hasColumn('residents', 'cell_phone')) {
                $table->renameColumn('phone', 'cell_phone');
            }
        });

        // Depois, adicionar as novas colunas em uma segunda operação
        Schema::table('residents', function (Blueprint $table) {
            // Verificar se a coluna cpf existe para usar como referência
            if (Schema::hasColumn('residents', 'cpf')) {
                $table->enum('person_type', ['fisica', 'juridica'])->default('fisica')->after('cpf');
            } else {
                $table->enum('person_type', ['fisica', 'juridica'])->default('fisica')->after('email');
            }
            
            // Usar cell_phone como referência (já foi renomeado na operação anterior)
            $table->string('commercial_phone')->nullable()->after('cell_phone');
            
            // Verificar se state existe, senão usar email como referência
            $afterStateColumn = Schema::hasColumn('residents', 'state') ? 'state' : 'email';
            $table->string('photo')->nullable()->after($afterStateColumn);
            $table->text('notes')->nullable()->after('photo');
            $table->boolean('is_porter')->default(false)->after('notes');
            $table->boolean('is_janitor')->default(false)->after('is_porter');
            $table->boolean('is_cleaning')->default(false)->after('is_janitor');
            $table->foreignId('apartment_id')->nullable()->after('condominium_id')->constrained('apartments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            if (Schema::hasColumn('residents', 'cell_phone')) {
                $table->renameColumn('cell_phone', 'phone');
            }

            $table->dropColumn([
                'person_type',
                'commercial_phone',
                'photo',
                'notes',
                'is_porter',
                'is_janitor',
                'is_cleaning',
                'apartment_id',
            ]);
        });
    }
};
