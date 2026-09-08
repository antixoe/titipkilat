<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('disputes',function(Blueprint $t){$t->id();$t->foreignId('order_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->text('reason');$t->string('status')->default('OPEN');$t->text('resolution')->nullable();$t->timestamps();$t->index('status');}); } public function down(): void {Schema::dropIfExists('disputes');} };
