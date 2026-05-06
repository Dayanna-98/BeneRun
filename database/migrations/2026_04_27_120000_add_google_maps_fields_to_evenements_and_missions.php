<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            $table->string('google_maps_url_evenement', 1000)->nullable()->after('lieu_evenement');
            $table->unsignedInteger('rayon_localisation_evenement')->nullable()->after('google_maps_url_evenement');
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->string('google_maps_url_mission', 1000)->nullable()->after('lieu_mission');
        });

        DB::table('evenements')
            ->select('id_evenement', 'latitude_evenement', 'longitude_evenement')
            ->whereNotNull('latitude_evenement')
            ->whereNotNull('longitude_evenement')
            ->orderBy('id_evenement')
            ->each(function (object $event): void {
                DB::table('evenements')
                    ->where('id_evenement', $event->id_evenement)
                    ->update([
                        'google_maps_url_evenement' => sprintf(
                            'https://www.google.com/maps?q=%s,%s',
                            $event->latitude_evenement,
                            $event->longitude_evenement
                        ),
                        'rayon_localisation_evenement' => 1000,
                    ]);
            });

        DB::table('missions')
            ->select('id_mission', 'latitude_mission', 'longitude_mission')
            ->whereNotNull('latitude_mission')
            ->whereNotNull('longitude_mission')
            ->orderBy('id_mission')
            ->each(function (object $mission): void {
                DB::table('missions')
                    ->where('id_mission', $mission->id_mission)
                    ->update([
                        'google_maps_url_mission' => sprintf(
                            'https://www.google.com/maps?q=%s,%s',
                            $mission->latitude_mission,
                            $mission->longitude_mission
                        ),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn('google_maps_url_mission');
        });

        Schema::table('evenements', function (Blueprint $table) {
            $table->dropColumn([
                'google_maps_url_evenement',
                'rayon_localisation_evenement',
            ]);
        });
    }
};