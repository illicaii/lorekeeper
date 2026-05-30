<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('loot_tables', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('loot_tables', 'data')) {
                $table->string('data', 512)->nullable()->default(null);
            }
        });

        Schema::table('loots', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('loots', 'subtable_id')) {
                $table->integer('subtable_id')->nullable()->default(null);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Note: reversing this migration will break status effects, you will need to rerun status effect migration if installed
        Schema::table('loot_tables', function (Blueprint $table) {
            //
            if (Schema::hasColumn('loot_tables', 'data')) {
                $table->dropColumn('data');
            }
        });

        Schema::table('loots', function (Blueprint $table) {
            //
            if (Schema::hasColumn('loots', 'subtable_id')) {
                $table->dropColumn('subtable_id');
            }
        });
    }
};
