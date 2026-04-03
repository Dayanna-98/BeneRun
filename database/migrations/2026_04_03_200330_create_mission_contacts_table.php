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
        Schema::create('mission_contacts', function (Blueprint $table) {
            $table->id('id_contact_mission');
            $table->unsignedBigInteger('id_mission');
            $table->foreign('id_mission')
                ->references('id_mission')
                ->on('missions')
                ->cascadeOnDelete();
            $table->string('nom_contact');
            $table->string('telephone_contact');
            $table->string('email_contact')->nullable();
            $table->boolean('est_contact_principal')->default(true);
            $table->boolean('est_contact_jour_j')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_contacts');
    }
};
