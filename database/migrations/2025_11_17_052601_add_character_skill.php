<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('character_skills', function (Blueprint $table) {
            $table->id();
            $table->integer('character_image_id')->unsigned();
            $table->integer('skill_id')->unsigned();
            $table->string('data')->nullable()->default(null);
            $table->enum('character_type', ['Character', 'Update'])->default('Character');
            $table->integer('charges')->unsigned()->default(0);
            $table->integer('xp')->unsigned()->default(0);
            $table->boolean('is_active')->default(0);
        });

        Schema::table('design_updates', function (Blueprint $table) {
            $table->boolean('has_skills')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('design_updates', function (Blueprint $table) {
            $table->dropColumn('has_skills');
        });

        Schema::dropIfExists('character_skills');
    }
};
