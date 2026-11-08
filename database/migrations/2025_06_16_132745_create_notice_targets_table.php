<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('notices')->onDelete('cascade');
            $table->morphs('target');
            $table->timestamps();
            $table->index(['notice_id', 'target_id', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_targets');
    }
};
