<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('orders',function(Blueprint $t){$t->string('invoice_photo')->nullable()->after('dp_required');$t->json('invoice_details')->nullable()->after('invoice_photo');}); } public function down(): void {Schema::table('orders',fn(Blueprint $t)=>$t->dropColumn(['invoice_photo','invoice_details']));} };
