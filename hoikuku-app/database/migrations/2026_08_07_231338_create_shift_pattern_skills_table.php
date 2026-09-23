<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_pattern_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_pattern_id'); // シフトパターンID
            $table->unsignedBigInteger('skill_id');         // 資格ID
            $table->integer('required_count')->default(1);  // その枠に必要な人数
            $table->timestamps();

            // 外部キー制約
            $table->foreign('shift_pattern_id')
                  ->references('id')
                  ->on('shift_patterns')
                  ->onDelete('cascade');

            $table->foreign('skill_id')
                  ->references('id')
                  ->on('skills')
                  ->onDelete('cascade');

            // 同じパターンに対して同じスキルが重複して登録されないようにする制約
            $table->unique(['shift_pattern_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_pattern_skills');
    }
};