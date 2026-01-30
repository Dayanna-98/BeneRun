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
            $table->unsignedBigInteger('id_benevole');
            $table->foreign('id_benevole')->references('id_benevole')->on('benevoles');
            $table->string('titre_badge');
            $table->integer('valeur_badge');
            $table->string('regle_auto_badge');

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
