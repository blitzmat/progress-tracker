<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('widgets', function (Blueprint $table) {
            // Drop old activity_id foreign key and column ONLY if they exist
            if (Schema::hasColumn('widgets', 'activity_id')) {
                // Try to drop foreign key first
                try {
                    $table->dropForeign(['activity_id']);
                } catch (\Exception) {}

                $table->dropColumn('activity_id');
            }

            // Add entry_id if not already present
            if (!Schema::hasColumn('widgets', 'entry_id')) {
                $table->foreignId('entry_id')->after('id')->constrained()->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table) {
            // Reverse the changes if needed
            if (Schema::hasColumn('widgets', 'entry_id')) {
                $table->dropForeign(['entry_id']);
                $table->dropColumn('entry_id');
            }

            if (!Schema::hasColumn('widgets', 'activity_id')) {
                $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            }
        });
    }
};
