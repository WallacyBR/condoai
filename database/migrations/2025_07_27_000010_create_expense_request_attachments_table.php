<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('expense_request_id')->constrained('expense_requests')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_type');
            $table->string('mime_type');
            $table->unsignedInteger('file_size');
            $table->timestamps();
            $table->index('expense_request_id');
            $table->index('file_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_request_attachments');
    }
};
