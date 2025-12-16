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
        Schema::create('pb_geo_bases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('long_ip1');
            $table->unsignedBigInteger('long_ip2');
            $table->char('country', 2);
            $table->string('city', 64);
            $table->dateTime('upd')->comment('актуальность');
            $table->unsignedInteger('oper')->default(0)->comment('Оператор сотовой связи');

            $table->index(['long_ip1', 'long_ip2']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pb_geo_bases');
    }
};
