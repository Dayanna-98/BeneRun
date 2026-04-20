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
        Schema::create('badges', function (Blueprint $table) {
            $table->id('id_badge');
            $table->string('titre_badge');
            $table->string('description_badge')->nullable();
            $table->string('icone_badge')->nullable();
            $table->unsignedInteger('score_badge')->default(0);
            $table->string('regle_auto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};