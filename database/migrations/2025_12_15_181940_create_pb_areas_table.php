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
        Schema::create('pb_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('okrug', 64)->default('');
            $table->string('autocod', 128)->default('');
            $table->unsignedInteger('capital')->default(0);
            $table->string('english', 64)->default('');
            $table->string('iso', 3)->default('');
            $table->char('country', 2);
            $table->integer('vid')->default(0)->comment('регион/область/и т.д.');
            $table->string('wiki', 1024)->nullable()->comment('ссылка на wikipedia без https://');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pb_areas');
    }
};
