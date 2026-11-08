<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $create = static function (string $name, \Closure $callback): void {
            if (! Schema::hasTable($name)) {
                Schema::create($name, $callback);
            }
        };

        $create('procurement_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('response_deadline');
            $table->string('status')->default('draft');
            $table->string('selection_criterion')->nullable();
            $table->text('selection_justification')->nullable();
            $table->boolean('publish_result')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        $create('procurement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->text('specification')->nullable();
            $table->string('unit')->default('un');
            $table->decimal('quantity', 12, 3);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        $create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->string('name');
            $table->string('document')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
            $table->unique(['condominium_id', 'document']);
        });

        $create('procurement_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supplier_name');
            $table->string('supplier_email')->nullable();
            $table->string('token', 80)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();
        });

        $create('supplier_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supplier_name');
            $table->string('supplier_document')->nullable();
            $table->string('supplier_email')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->decimal('freight_amount', 15, 2)->default(0);
            $table->string('payment_terms')->nullable();
            $table->string('delivery_terms')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique('procurement_invitation_id');
        });

        $create('supplier_quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('procurement_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('quantity', 12, 3);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['supplier_quote_id', 'procurement_item_id'], 'supplier_quote_item_unique');
        });

        $create('supplier_quote_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_quote_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedInteger('file_size');
            $table->timestamps();
        });

        $create('procurement_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('procurement_item_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('selected_by')->constrained('users')->cascadeOnDelete();
            $table->string('criterion');
            $table->text('justification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_awards');
        Schema::dropIfExists('supplier_quote_attachments');
        Schema::dropIfExists('supplier_quote_items');
        Schema::dropIfExists('supplier_quotes');
        Schema::dropIfExists('procurement_invitations');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('procurement_items');
        Schema::dropIfExists('procurement_requests');
    }
};
