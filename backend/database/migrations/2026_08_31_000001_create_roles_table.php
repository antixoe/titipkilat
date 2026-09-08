<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('roles', function(Blueprint $t){$t->id();$t->string('name')->unique();}); Schema::table('users',function(Blueprint $t){$t->foreignId('role_id')->nullable()->after('id')->constrained()->nullOnDelete();}); } public function down(): void { Schema::table('users',fn(Blueprint $t)=>$t->dropConstrainedForeignId('role_id')); Schema::dropIfExists('roles'); } };
