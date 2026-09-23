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
        Schema::create('mental_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('weather'); // sunny, cloudy, rainy, thunderstorm
            $table->tinyInteger('sleep_hours'); // 睡眠時間
            $table->tinyInteger('relation_stress'); // 人間関係の悩みレベル (1-5)
            $table->tinyInteger('physical_fatigue'); // 体力的疲れレベル (1-5)
            $table->text('comment')->nullable();
            $table->date('checked_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mental_check_ins');
    }
};
