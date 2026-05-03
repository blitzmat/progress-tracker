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
            $table->unsignedSmallInteger('default_tempo')->nullable()->after('description');
            $table->string('default_sound')->nullable()->default('beep');
            $table->float('default_volume')->nullable()->default(0.5);
            $table->string('default_time_signature')->nullable()->default('4/4');
            $table->string('default_finger_note_type')->nullable();
            $table->string('default_finger_pattern')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            //
        });
    }
};
