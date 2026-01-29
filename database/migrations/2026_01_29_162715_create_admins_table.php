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
        Schema::create('admins', function (Blueprint $table) {
            $table->id('id_admin');
              // clé étrangère vers utilisateur (héritage)
            $table->foreignId('id_utilisateur')
                ->constrained('utilisateurs', 'id_utilisateur')
                ->onDelete('cascade');
            $table->boolean('est_organisateur_admin')->default(false);
            $table->enum('permission_admin', ['visualiser_benevoles', 'suppression_benevoles', 'creer_course', 'attribuer_badge', 'creer_certificat'])->default('visualiser_benevoles');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
