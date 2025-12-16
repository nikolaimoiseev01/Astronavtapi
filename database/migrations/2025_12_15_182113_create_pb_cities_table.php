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
        Schema::create('pb_cities', function (Blueprint $table) {
            $table->id();

            $table->string('name', 64);
            $table->unsignedInteger('area')->default(0)->comment('область');
            $table->string('telcod', 256)->default('');
            $table->float('latitude')->nullable()->comment('широта');
            $table->float('longitude')->nullable()->comment('долгота');
            $table->float('time_zone')->nullable()->comment('Время относительно UTC(GMT)');
            $table->string('tz', 64)->charset('ascii')->collation('ascii_general_ci')->default('')
                ->comment('название временной зоны');
            $table->string('english', 64)->default('');
            $table->unsignedInteger('rajon')->default(0)->comment('район области');
            $table->integer('sub_rajon')->default(0)->comment('подрайон в районе');
            $table->char('country', 2);
            $table->char('sound', 4)->default('');
            $table->tinyInteger('level')->default(0)
                ->comment('1-столица Округа, 2-крупный город, 3-небольшой населенный пункт');
            $table->string('iso', 3)->default('');
            $table->tinyInteger('vid')->unsigned()->default(0)
                ->comment('1-город, 2-поселок, 3-село, 4-деревня, 5-станица, 6-хутор');
            $table->string('post', 512)->default('')->comment('Почтовый код');
            $table->unsignedInteger('geonameid')->nullable();
            $table->string('wiki', 1024)->nullable()->comment('ссылка на wikipedia без https://');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pb_cities');
    }
};
