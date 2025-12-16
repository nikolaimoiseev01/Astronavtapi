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
        Schema::create('pb_rajons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->unsignedInteger('area');
            $table->char('country', 2);
            $table->unsignedInteger('capital')->default(0);
            $table->string('english', 64)->default('');
            $table->integer('vid')->default(0)->comment('регион/область/и т.д.');
            $table->integer('parent')->default(0)->comment('родительский район для подрайона');
            $table->string('iso', 5)->default('');
            $table->string('wiki', 1024)->nullable()->comment('ссылка на wikipedia без https://');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pb_rajons');
    }
};
