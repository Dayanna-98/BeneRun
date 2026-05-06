<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('partage_localisation_directe_utilisateur')->default(false)->after('raison_suspension_utilisateur');
            $table->decimal('latitude_localisation_directe_utilisateur', 10, 7)->nullable()->after('partage_localisation_directe_utilisateur');
            $table->decimal('longitude_localisation_directe_utilisateur', 10, 7)->nullable()->after('latitude_localisation_directe_utilisateur');
            $table->timestamp('date_localisation_directe_utilisateur')->nullable()->after('longitude_localisation_directe_utilisateur');
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