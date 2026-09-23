<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. 資格マスタテーブル
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 例: 保育士、看護師、幼稚園教諭
            $table->timestamps();
        });

        // 2. スタッフ・スキル中間テーブル（多対多）
        Schema::create('staff_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id'); // スタッフID (usersテーブル等を想定)
            $table->unsignedBigInteger('skill_id'); // 資格ID
            $table->timestamps();

            // 外部キー制約
            $table->foreign('skill_id')
                  ->references('id')
                  ->on('skills')
                  ->onDelete('cascade');

            // 同じスタッフに同じ資格が重複して登録されるのを防ぐユニーク制約
            $table->unique(['staff_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_skills');
        Schema::dropIfExists('skills');
    }
};
