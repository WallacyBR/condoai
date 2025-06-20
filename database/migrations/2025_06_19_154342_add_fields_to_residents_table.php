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
            // Dados básicos
            $table->string('rg')->nullable()->after('name');
            $table->string('cpf')->nullable()->after('rg');
            
            // Contato - usar cell_phone se existir, senão phone
            $afterPhoneColumn = Schema::hasColumn('residents', 'cell_phone') ? 'cell_phone' : 'phone';
            $table->string('whatsapp')->nullable()->after($afterPhoneColumn);
            
            // Endereço para boletos
            $table->enum('billing_address_type', ['condominium', 'custom'])->default('condominium')->after('whatsapp');
            $table->string('cep')->nullable()->after('billing_address_type');
            $table->string('address')->nullable()->after('cep');
            $table->string('neighborhood')->nullable()->after('address');
            $table->string('city')->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn([
                'rg',
                'cpf',
                'whatsapp',
                'billing_address_type',
                'cep',
                'address',
                'neighborhood',
                'city',
                'state'
            ]);
        });
    }
};
