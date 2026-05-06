<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
    $table->boolean('partage_localisation_directe_utilisateur')->default(false);
    $table->decimal('latitude_localisation_directe_utilisateur', 10, 7)->nullable();
    $table->decimal('longitude_localisation_directe_utilisateur', 10, 7)->nullable();
    $table->timestamp('date_localisation_directe_utilisateur')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'partage_localisation_directe_utilisateur',
                'latitude_localisation_directe_utilisateur',
                'longitude_localisation_directe_utilisateur',
                'date_localisation_directe_utilisateur',
            ]);
        });
    }
};