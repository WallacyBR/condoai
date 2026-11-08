<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_passkeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->string('name')->default('Passkey');
            $table->text('credential_id');
            $table->char('credential_id_hash', 64)->unique();
            $table->longText('credential_public_key');
            $table->json('credential_record');
            $table->string('user_handle', 128);
            $table->json('transports')->nullable();
            $table->unsignedBigInteger('sign_count')->default(0);
            $table->boolean('backup_eligible')->nullable();
            $table->boolean('backup_status')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['condominium_id', 'user_id']);
            $table->index(['condominium_id', 'revoked_at']);
        });

        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('condominium_id')->nullable()->constrained('condominiums')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('method', 32);
            $table->string('status', 32);
            $table->string('reason')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['condominium_id', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['method', 'status']);
        });

        Schema::create('webauthn_challenges', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('condominium_id')->nullable()->constrained('condominiums')->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('type', 32);
            $table->string('challenge', 512);
            $table->json('options');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'expires_at']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webauthn_challenges');
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('user_passkeys');
    }
};
