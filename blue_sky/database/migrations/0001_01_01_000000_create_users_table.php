<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->max(255);
            $table->string('email')->unique()->max(255);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->min(8)->max(30);
            $table->foreignId('fk_region_id')->constrained('regions')->onUpdate('cascade');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        DB::table('users')->insert([
            'name' => 'Fortaleza',
            'email' => 'ce@bizsys.com.br',
            'password' => Hash::make('123456789'),
            'fk_region_id' => 1,
        ]);
        DB::table('users')->insert([
            'name' => 'Recife',
            'email' => 'pe@bizsys.com.br',
            'password' => Hash::make('123456789'),
            'fk_region_id' => 2,
        ]);
        DB::table('users')->insert([
            'name' => 'Rio de Janeiro',
            'email' => 'rj@bizsys.com.br',
            'password' => Hash::make('123456789'),
            'fk_region_id' => 3,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
