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
        Schema::create('pb_cinonims', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('sinonim', 512)->collation('utf8mb4_0900_ai_ci');
            $table->string('tbl', 64)->default('');
            $table->integer('lang')->default(0)->comment('язык');
            $table->integer('tbl_id')->default(0)->comment('id в таблице при переименовании');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pb_cinonims');
    }
};
