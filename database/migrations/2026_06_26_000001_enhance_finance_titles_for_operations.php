<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_installment_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('description');
            $table->string('kind')->default('installment');
            $table->decimal('total_amount', 15, 2);
            $table->unsignedSmallInteger('installments')->default(1);
            $table->date('first_due_date');
            $table->decimal('expected_return_rate', 8, 4)->nullable();
            $table->timestamps();
        });

        foreach (['payables', 'receivables'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->date('competence_date')->nullable()->after('due_date');
                $table->string('counterpart_name')->nullable()->after('amount');
                $table->string('reference')->nullable()->after('counterpart_name');
                $table->foreignId('expense_request_id')->nullable()->after('bank_id')
                    ->constrained('expense_requests')->nullOnDelete();
                $table->foreignId('installment_group_id')->nullable()->after('expense_request_id')
                    ->constrained('finance_installment_groups')->nullOnDelete();
            });

            DB::table($tableName)->whereNull('competence_date')->update([
                'competence_date' => DB::raw('due_date'),
            ]);
        }
    }

    public function down(): void
    {
        foreach (['payables', 'receivables'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('expense_request_id');
                $table->dropConstrainedForeignId('installment_group_id');
                $table->dropColumn(['competence_date', 'counterpart_name', 'reference']);
            });
        }

        Schema::dropIfExists('finance_installment_groups');
    }
};
