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
            $table->boolean('override_default_caps')->default(0);
            $table->integer('ovr_level_cap')->unsigned()->default(0);
            $table->integer('ovr_charge_cap')->unsigned()->default(0);
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
            $table->string('hash')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_categories');
    }
};
