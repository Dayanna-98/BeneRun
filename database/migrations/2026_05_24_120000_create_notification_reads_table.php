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
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id('id_notification_read');
            $table->unsignedBigInteger('id_utilisateur');
            $table->string('notification_key', 255);
            $table->timestamp('read_at');
            $table->timestamps();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(['id_utilisateur', 'notification_key'], 'notification_reads_user_key_unique');
            $table->index(['id_utilisateur', 'read_at'], 'notification_reads_user_read_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};
