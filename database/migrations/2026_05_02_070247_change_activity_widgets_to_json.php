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
        Schema::table('activities', function (Blueprint $table) {
            // Drop the individual columns
            $table->dropColumn([
                'default_tempo',
                'default_sound',
                'default_volume',
                'default_time_signature',
                'default_finger_note_type',
                'default_finger_pattern',
            ]);

            // Add a single JSON column for all widget settings
            $table->json('widget_settings')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        // reverse if needed (not necessary for now)
    }

};
