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
        Schema::create('app_token', function (Blueprint $table) {
            $table->id();
            $table->string('token')->max(255);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('app_token')->insert([
            'token' => 'w4T5cB8tNwltefkFKV0bKOQ9R4S5eDRLMnzBpIxpIBdspbz0KMz1psBHaspNo3yl',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_token');
    }
};