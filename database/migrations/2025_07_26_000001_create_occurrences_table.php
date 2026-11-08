<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\OccurrenceStatus;
use App\Enums\OccurrencePriority;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occurrences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('status', OccurrenceStatus::values())->default(OccurrenceStatus::OPEN->value);
            $table->enum('priority', OccurrencePriority::values())->default(OccurrencePriority::MEDIUM->value);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('occurrences');
    }
};
