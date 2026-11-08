<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('pickup_location')->nullable()->after('photo');
            $table->text('observation')->nullable()->after('pickup_location');
            $table->foreignId('user_id')->nullable()->constrained('users')->after('apartment_id');
            $table->string('signature')->nullable()->after('retrieved_at');
            $table->string('document_photo')->nullable()->after('signature');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['pickup_location', 'observation', 'user_id', 'signature', 'document_photo']);
        });
    }
};
