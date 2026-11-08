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
        Schema::create('notice_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('notices')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_type')->comment("Ex: 'image', 'document', 'pdf', 'video'"); // Adicionado comentário conforme plano
            $table->string('mime_type');
            $table->unsignedInteger('file_size')->comment('File size in bytes'); // Adicionado unsigned e comentário
            $table->timestamps();

            $table->index('notice_id');
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice_attachments');
    }
};
