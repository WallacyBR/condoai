<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->boolean('notification_email')->default(true)->after('condominium_id');
            $table->boolean('notification_push')->default(true)->after('notification_email');
            $table->boolean('notification_sms')->default(false)->after('notification_push');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'notification_email', 'notification_push', 'notification_sms']);
        });
    }
};
