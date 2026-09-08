<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('weight_lbs')->default(1)->after('item_description');
            $table->string('origin_zone')->nullable()->after('trip_reference');
            $table->string('destination_zone')->nullable()->after('origin_zone');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['weight_lbs', 'origin_zone', 'destination_zone']);
        });
    }
};
