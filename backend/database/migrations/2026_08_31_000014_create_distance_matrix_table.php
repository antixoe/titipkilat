<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('distance_matrix',function(Blueprint $t){$t->id();$t->string('origin_zone');$t->string('destination_zone');$t->unsignedInteger('distance_km');$t->timestamps();$t->unique(['origin_zone','destination_zone']);}); } public function down(): void {Schema::dropIfExists('distance_matrix');} };
