<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('wallet_transactions',function(Blueprint $t){$t->unsignedBigInteger('debit')->default(0)->after('type');$t->unsignedBigInteger('credit')->default(0)->after('debit');}); } public function down(): void {Schema::table('wallet_transactions',fn(Blueprint $t)=>$t->dropColumn(['debit','credit']));} };
