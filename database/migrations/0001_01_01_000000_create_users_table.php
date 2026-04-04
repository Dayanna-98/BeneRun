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
        Schema::create('users', function (Blueprint $table) {
        $table->id('id_utilisateur');
        $table->string('nom_utilisateur');
        $table->string('prenom_utilisateur');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamp('email_verified_at')->nullable();
        $table->enum('role_utilisateur', ['bénévole','responsable','admin','superadmin'])->default('bénévole');
        $table->string('telephone_utilisateur')->nullable();
        $table->string('adresse_utilisateur')->nullable();
        $table->date('date_naissance_utilisateur')->nullable();
        $table->string('allergies_utilisateur')->nullable();
        $table->string('problemes_sante_utilisateur')->nullable();
        $table->boolean('possede_permis_utilisateur')->default(false);
        $table->boolean('est_motorise_utilisateur')->default(false);
        $table->boolean('possede_vehicule_utilisateur')->default(false);
        $table->enum('taille_tshirt_utilisateur', ['XS', 'S', 'M', 'L', 'XL'])->nullable();
        $table->boolean('est_anonyme_utilisateur')->default(false);
        $table->boolean('est_suspendu_utilisateur')->default(false);
        $table->string('raison_suspension_utilisateur')->nullable();
        $table->string('permissions_utilisateur')->nullable();
        $table->unsignedInteger('nombre_missions_utilisateur')->default(0);
        $table->rememberToken();
        $table->timestamps();
        $table->softDeletes();
});


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
