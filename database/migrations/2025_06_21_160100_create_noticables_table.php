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
        Schema::create('noticables', function (Blueprint $table) {
            $table->foreignId('notice_id')->constrained('notices')->onDelete('cascade');
            $table->morphs('noticable'); // Isso cria noticable_id (unsignedBigInteger) e noticable_type (string)

            $table->primary(['notice_id', 'noticable_id', 'noticable_type'], 'noticables_primary_key'); // Nome explícito para a chave primária composta
            $table->index('notice_id');
            $table->index(['noticable_id', 'noticable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticables');
    }
};
