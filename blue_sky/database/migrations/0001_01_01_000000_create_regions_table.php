<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('city')->max(255);
            $table->string('state_uf')->min(2)->max(2);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('regions')->insert([
            'city' => 'Fortaleza',
            'state_uf' => 'CE',
        ]);

        DB::table('regions')->insert([
            'city' => 'Recife',
            'state_uf' => 'PE',
        ]);

        DB::table('regions')->insert([
            'city' => 'Rio de janeiro',
            'state_uf' => 'RJ',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropAllTables('regions');
    }
};