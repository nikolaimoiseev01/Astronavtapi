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
        Schema::create('incarnations', function (Blueprint $table) {
            $table->id();
            $table->text('profile')->collation('utf8mb4_unicode_ci');
            $table->text('quarter')->collation('utf8mb4_unicode_ci');
            $table->text('cross')->collation('utf8mb4_unicode_ci');
            $table->text('description')->collation('utf8mb4_unicode_ci');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incarnations');
    }
};
