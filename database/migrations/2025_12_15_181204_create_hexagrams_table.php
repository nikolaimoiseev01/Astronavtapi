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
        Schema::create('hexagrams', function (Blueprint $table) {
            $table->id();

            $table->decimal('hexagram', 10, 1);
            $table->decimal('start', 10, 4);
            $table->decimal('end', 10, 4);

            $table->text('sun');
            $table->text('earth');
            $table->text('moon');
            $table->text('mercury');
            $table->text('venus');
            $table->text('mars');
            $table->text('jupiter');
            $table->text('saturn');
            $table->text('uranus');
            $table->text('neptune');
            $table->text('pluto');

            $table->text('circuit');
            $table->integer('quarter');

            $table->text('zodiac_sign');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hexagrams');
    }
};
