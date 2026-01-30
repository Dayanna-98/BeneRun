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
        Schema::create('telephones', function (Blueprint $table) {
            $table->id('id_telephone');
            $table->unsignedBigInteger('id_mission');
            $table->foreign('id_mission')->references('id_mission')->on('missions');
            $table->unsignedBigInteger('id_course');
            $table->foreign('id_course')->references('id_course')->on('courses');
            $table->string('description_telephone');
            $table->integer('numero_telephone');
            $table->string('detail_telephone');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telephones');
    }
};
