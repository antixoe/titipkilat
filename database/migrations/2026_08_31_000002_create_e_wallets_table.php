<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('e_wallets',function(Blueprint $t){$t->id();$t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();$t->unsignedBigInteger('balance')->default(0);$t->timestamps();}); } public function down(): void {Schema::dropIfExists('e_wallets');} };
