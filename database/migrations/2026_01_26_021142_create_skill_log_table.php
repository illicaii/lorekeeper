<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('skill_log', function (Blueprint $table) {
            $table->id();
            $table->integer('character_id')->nullable();
            $table->integer('sender_id')->nullable();
            $table->text('log');
            $table->string('log_type');
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('skill_log');
    }
};
