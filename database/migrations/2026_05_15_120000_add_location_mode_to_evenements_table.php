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
            $table->string('mode_localisation_evenement', 20)
                ->default('manual')
                ->after('rayon_localisation_evenement');
        });

        DB::table('evenements')
            ->whereNull('mode_localisation_evenement')
            ->update(['mode_localisation_evenement' => 'manual']);
    }

    public function down(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            $table->dropColumn('mode_localisation_evenement');
        });
    }
};
