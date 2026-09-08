<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('wallet_transactions',function(Blueprint $t){$t->id();$t->foreignId('e_wallet_id')->constrained()->cascadeOnDelete();$t->string('type');$t->unsignedBigInteger('amount');$t->unsignedBigInteger('fee')->default(0);$t->string('description');$t->string('reference')->nullable();$t->timestamps();}); } public function down(): void {Schema::dropIfExists('wallet_transactions');} };
