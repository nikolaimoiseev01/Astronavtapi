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
        Schema::create('pb_telcodes', function (Blueprint $table) {
            $table->id();
            $table->integer('okrug')->comment('Код региона');
            $table->unsignedInteger('city')->comment('Код города');
            $table->unsignedInteger('oper')->comment('Код оператора');
            $table->string('deffrom', 15);
            $table->string('defto', 15);
            $table->char('country', 2);
            $table->integer('mnc')->default(0);
            $table->char('route', 5)->default('')->comment('маршрут');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pb_telcodes');
    }
};
