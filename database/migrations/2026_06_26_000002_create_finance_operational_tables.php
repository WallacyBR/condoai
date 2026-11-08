<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('payable_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('receivable_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->date('settled_on');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('proof_path')->nullable();
            $table->timestamps();
            $table->index(['condominium_id', 'settled_on']);
        });

        Schema::table('expense_requests', function (Blueprint $table) {
            $table->string('title')->nullable()->after('approved_by');
            $table->string('reference')->nullable()->after('title');
            $table->foreignId('chart_account_id')->nullable()->after('condominium_id')
                ->constrained('chart_accounts')->nullOnDelete();
            $table->foreignId('payable_id')->nullable()->after('approved_by')
                ->constrained('payables')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('submitted_at');
            $table->text('rejection_reason')->nullable()->after('description');
        });

        Schema::create('expense_approval_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('minimum_amount', 15, 2)->default(0);
            $table->decimal('maximum_amount', 15, 2)->nullable();
            $table->unsignedSmallInteger('approval_order')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('expense_request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_approval_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('approval_order');
            $table->string('decision');
            $table->text('justification');
            $table->timestamp('decided_at');
            $table->timestamps();
            $table->unique(['expense_request_id', 'expense_approval_rule_id'], 'expense_request_approval_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_request_approvals');
        Schema::dropIfExists('expense_approval_rules');
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chart_account_id');
            $table->dropConstrainedForeignId('payable_id');
            $table->dropColumn(['title', 'reference', 'submitted_at', 'rejected_at', 'rejection_reason']);
        });
        Schema::dropIfExists('financial_settlements');
    }
};
