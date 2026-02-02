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
        Schema::create('benevoles', function (Blueprint $table) {
            $table->id('id_benevole');
            $table->unsignedBigInteger('id_utilisateur'); 
            $table->foreign('id_utilisateur')
                  ->references('id_utilisateur')   
                  ->on('users')
                  ->onDelete('cascade');

            $table->integer('nb_missions_benevole');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benevoles');
    }
};
