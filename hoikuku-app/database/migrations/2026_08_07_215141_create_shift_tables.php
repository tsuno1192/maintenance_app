<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. 固定シフトマスタ
        Schema::create('shift_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // パターン名 (例: 早番、遅番、日勤)
            $table->time('start_time'); // 開始時間
            $table->time('end_time'); // 終了時間
            $table->timestamps();
        });

        // 2. シフト希望提出データ
        Schema::create('shift_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id'); // スタッフID (usersテーブル等と紐付け)
            $table->date('target_date'); // 対象日
            $table->tinyInteger('request_type'); // 希望区分 (0: 休日, 1: 固定パターン, 2: 自由時間)
            
            // 固定パターンのID (request_type = 1 の場合に使用)
            $table->unsignedBigInteger('shift_pattern_id')->nullable();
            
            // 自由入力の時間 (request_type = 2 の場合に使用)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            $table->text('memo')->nullable(); // スタッフからの申し送り事項
            $table->timestamps();

            // 外部キー制約
            $table->foreign('shift_pattern_id')
                  ->references('id')
                  ->on('shift_patterns')
                  ->onDelete('set null');
                  
            // 同じスタッフが同日に重複して希望を出すのを防ぐユニーク制約（必要に応じて）
            $table->unique(['staff_id', 'target_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_requests');
        Schema::dropIfExists('shift_patterns');
    }
};