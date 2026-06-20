<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('skill_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('skill_id')->unsigned();
            $table->string('tag');
            $table->text('data')->nullable();
            $table->boolean('is_active')->default(0);
            $table->integer('reset_period')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('skill_tags');
    }
};
