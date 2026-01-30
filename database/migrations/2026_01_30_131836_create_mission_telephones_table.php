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
        Schema::create('mission_telephones', function (Blueprint $table) {
            $table->id('id_mission_telephone');
            $table->unsignedBigInteger('id_mission');
            $table->foreign('id_mission')->references('id_mission')->on('missions');
            $table->unsignedBigInteger('id_telephone');
            $table->foreign('id_telephone')->references('id_telephone')->on('telephones');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_telephones');
    }
};
