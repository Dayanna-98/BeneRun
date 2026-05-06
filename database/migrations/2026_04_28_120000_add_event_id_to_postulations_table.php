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
        Schema::table('postulations', function (Blueprint $table) {
            $table->unsignedBigInteger('id_mission')->nullable()->change();

            if (!Schema::hasColumn('postulations', 'id_evenement')) {
                $table->unsignedBigInteger('id_evenement')->nullable()->after('id_mission');
                $table->foreign('id_evenement')
                    ->references('id_evenement')
                    ->on('evenements')
                    ->nullOnDelete();
            }

            $table->unique(['id_evenement', 'id_utilisateur'], 'postulations_evenement_utilisateur_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulations', function (Blueprint $table) {
            $table->dropUnique('postulations_evenement_utilisateur_unique');

            $table->unsignedBigInteger('id_mission')->nullable(false)->change();

            if (Schema::hasColumn('postulations', 'id_evenement')) {
                $table->dropConstrainedForeignId('id_evenement');
            }
        });
    }
};
