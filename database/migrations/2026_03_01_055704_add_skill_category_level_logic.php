<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('skill_categories', function (Blueprint $table) {
            $table->boolean('is_levelable')->default(0);
            $table->integer('level_base')->default(10);
            $table->float('level_multiplier')->default(1);
            $table->boolean('randomize_firstLevel')->default(0);
            $table->integer('random_level_min')->default(0);
            $table->integer('random_level_max')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('skill_categories', function (Blueprint $table) {
            $table->dropColumn('is_levelable');
            $table->dropColumn('level_base');
            $table->dropColumn('level_multiplier');
            $table->dropColumn('randomize_firstLevel');
            $table->dropColumn('random_level_min');
            $table->dropColumn('random_level_max');
        });
    }
};
