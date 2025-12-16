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
        Schema::create('pb_countries', function (Blueprint $table) {
            $table->char('id', 2)->primary();

            $table->string('name', 64);
            $table->string('fullname', 256)->default('');
            $table->string('english', 64)->default('');
            $table->char('country_code3', 3)->default('');
            $table->char('iso', 3)->default('');
            $table->char('telcod', 4)->default('');
            $table->tinyInteger('telcod_len')->default(0)->comment('длина номера телефона');
            $table->char('location', 10)->nullable();
            $table->unsignedInteger('capital')->default(0)->comment('Столица');
            $table->integer('mcc')->default(0)->comment('Код страны телефонных операторов');
            $table->string('lang', 64)->default('')->comment('Основной язык');
            $table->string('langcod', 12)->default('')->comment('коды языков через ,');
            $table->string('wiki', 1024)->nullable()->comment('ссылка на wikipedia без https://');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pb_countries');
    }
};
