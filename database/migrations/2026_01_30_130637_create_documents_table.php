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
        Schema::create('documents', function (Blueprint $table) {
            $table->id('id_document');
            $table->unsignedBigInteger('id_mission');
            $table->foreign('id_mission')->references('id_mission')->on('missions');
            $table->unsignedBigInteger('id_course');
            $table->foreign('id_course')->references('id_course')->on('courses');
            $table->string('nom_fichier_document');
            $table->string('type_mime_document');
            $table->dateTime('date_document');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
