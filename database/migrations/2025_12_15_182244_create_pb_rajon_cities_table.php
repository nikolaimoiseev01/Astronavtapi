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
        Schema::create('pb_rajon_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->unsignedInteger('city')->default(0)->comment('Город');
            $table->string('english', 64)->default('');
            $table->unsignedInteger('parent')->default(0);
            $table->text('polygon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pb_rajon_cities');
    }
};
