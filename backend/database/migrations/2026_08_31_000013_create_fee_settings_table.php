<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('fee_settings',function(Blueprint $t){$t->id();$t->string('key')->unique();$t->unsignedBigInteger('amount')->default(0);$t->string('description')->nullable();$t->timestamps();}); } public function down(): void {Schema::dropIfExists('fee_settings');} };
