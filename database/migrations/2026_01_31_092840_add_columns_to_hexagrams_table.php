<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hexagrams', function (Blueprint $table) {
            $table->string('yin_yang_balance')->nullable();
            $table->string('role')->nullable();
            $table->string('mind')->nullable();
            $table->string('decision')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hexagrams', function (Blueprint $table) {
            //
        });
    }
};
