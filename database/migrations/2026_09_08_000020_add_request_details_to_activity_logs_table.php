<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('metadata');
            $table->string('ip_location')->nullable()->after('ip_address');
            $table->text('user_agent')->nullable()->after('ip_location');
            $table->string('method', 10)->nullable()->after('user_agent');
            $table->string('route_name')->nullable()->after('method');
            $table->index('action');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['action']);
            $table->dropIndex(['ip_address']);
            $table->dropColumn(['ip_address', 'ip_location', 'user_agent', 'method', 'route_name']);
        });
    }
};
