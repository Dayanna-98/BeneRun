<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Les champs live location ont été retirés - migration conservée pour l'historique
    }

    public function down(): void
    {
        // Les champs live location ont été retirés - aucune action de rollback
    }
};