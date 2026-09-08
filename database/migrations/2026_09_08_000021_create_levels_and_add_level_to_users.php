<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('level_id')->nullable()->after('role_id')->constrained('levels')->nullOnDelete();
        });

        foreach ([['Starter', 'starter', 'Akses dasar platform', 1], ['Verified', 'verified', 'Akun terverifikasi', 2], ['Professional', 'professional', 'Akses dan limit lebih tinggi', 3], ['Enterprise', 'enterprise', 'Akses penuh untuk akun bisnis', 4]] as [$name, $slug, $description, $sort]) {
            DB::table('levels')->insert(['name' => $name, 'slug' => $slug, 'description' => $description, 'sort_order' => $sort, 'created_at' => now(), 'updated_at' => now()]);
        }

        DB::table('users')->whereNull('level_id')->update(['level_id' => DB::table('levels')->where('slug', 'starter')->value('id')]);
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('level_id'));
        Schema::dropIfExists('levels');
    }
};
