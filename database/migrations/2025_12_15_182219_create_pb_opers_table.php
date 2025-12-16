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
        Schema::create('pb_opers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 512);
            $table->tinyInteger('mobile')->unsigned()->default(0);
            $table->unsignedInteger('mvno')->default(0)->comment('Виртуальный оператор');
            $table->char('country', 2);
            $table->unsignedInteger('mnc')->default(0);
            $table->string('brand', 64)->default('');
            $table->string('url', 128)->default('')->comment('сайт оператора');
            $table->timestamp('deleted')->nullable()->comment('дата удаления');
            $table->unsignedBigInteger('inn')->default(0)->comment('ИНН');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pb_opers');
    }
};
