<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('orders',function(Blueprint $t){$t->foreignId('traveler_id')->nullable()->after('courier_id')->constrained('users')->nullOnDelete();$t->string('purchase_photo')->nullable()->after('invoice_details');}); } public function down(): void {Schema::table('orders',function(Blueprint $t){$t->dropConstrainedForeignId('traveler_id');$t->dropColumn('purchase_photo');});} };
