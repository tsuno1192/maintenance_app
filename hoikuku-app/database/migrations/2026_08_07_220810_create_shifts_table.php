<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id'); // スタッフID
            $table->date('target_date'); // 対象日
            
            // 割り当てられた固定パターンのID（自由時間の場合はnullableにして開始・終了を持つ構成に拡張可能）
            $table->unsignedBigInteger('shift_pattern_id')->nullable();
            
            // 自由時間のシフトに対応する場合の予備カラム（必要に応じて）
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->tinyInteger('status')->default(0); // ステータス (0: 自動割り当て案, 1: 管理者調整中, 2: 確定)
            
            $table->timestamps();

            // 外部キー制約
            $table->foreign('shift_pattern_id')
                  ->references('id')
                  ->on('shift_patterns')
                  ->onDelete('set null');

            // 同一スタッフが同日に複数の確定シフトを持たないようにする制約
            $table->unique(['staff_id', 'target_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};