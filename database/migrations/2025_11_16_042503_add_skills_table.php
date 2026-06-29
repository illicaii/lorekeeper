<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('skill_abrv');
            $table->text('description')->nullable();
            $table->text('parsed_description')->nullable();
            $table->integer('skill_category_id')->unsigned()->nullable()->default(null);
            $table->integer('species_id')->unsigned()->nullable()->default(null);
            $table->integer('parent_id')->unsigned()->nullable()->default(null);
            $table->integer('parent_level')->unsigned()->nullable()->default(null);
            $table->integer('skill_type')->unsigned()->nullable()->default(null);
            $table->boolean('has_image')->default(0);
            $table->boolean('is_visible')->default(0);
            $table->boolean('is_backend')->default(0);
            $table->boolean('override_default_caps')->default(0);
            $table->integer('ovr_level_cap')->unsigned()->default(0);
            $table->integer('ovr_charge_cap')->unsigned()->default(0);
            $table->integer('ovr_reset_frequency')->default(0);
            $table->string('ovr_reset_period')->nullable()->default(null);
            $table->string('hash')->nullable()->default(null);
        });

        Schema::create('skill_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('parsed_description')->nullable();
            $table->boolean('has_image')->default(0);
            $table->boolean('is_visible')->default(0);
            $table->boolean('is_default')->default(0);
            $table->integer('sort')->unsigned()->default(0);
            $table->integer('max_level')->unsigned()->default(0);
            $table->integer('max_charge')->unsigned()->default(0);
            $table->integer('reset_frequency')->default(0);
            $table->string('reset_period')->nullable()->default(null);
            $table->string('hash')->nullable()->default(null);
            $table->boolean('is_levelable')->default(0);
            $table->integer('level_base')->default(10);
            $table->float('level_multiplier')->default(1);
            $table->boolean('randomize_firstLevel')->default(0);
            $table->integer('random_level_min')->default(0);
            $table->integer('random_level_max')->default(0);
        });

        Schema::create('skill_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('skill_id')->unsigned();
            $table->string('tag');
            $table->text('data')->nullable();
            $table->boolean('is_active')->default(0);
        });

        Schema::create('character_skills', function (Blueprint $table) {
            $table->id();
            $table->integer('character_image_id')->unsigned();
            $table->integer('skill_id')->unsigned();
            $table->string('data')->nullable()->default(null);
            $table->enum('character_type', ['Character', 'Update'])->default('Character');
            $table->integer('charges')->unsigned()->default(0);
            $table->timestamp('reset_time')->nullable()->default(null);
            $table->integer('xp')->unsigned()->default(0);
        });

        Schema::create('skill_log', function (Blueprint $table) {
            $table->id();
            $table->integer('character_id')->nullable();
            $table->integer('sender_id')->nullable();
            $table->text('log');
            $table->string('log_type');
            $table->text('data')->nullable();
            $table->timestamps();
        });

        Schema::table('design_updates', function (Blueprint $table) {
            $table->boolean('has_skills')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_categories');
        Schema::dropIfExists('skill_tags');
        Schema::dropIfExists('character_skills');
        Schema::dropIfExists('skill_log');

        Schema::table('design_updates', function (Blueprint $table) {
            $table->dropColumn('has_skills');
        });
    }
};
