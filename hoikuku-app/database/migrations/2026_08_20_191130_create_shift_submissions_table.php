<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_submissions', function (Blueprint $table) {
            $table->id();
            // スタッフ（ユーザー）との紐付け
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // 提出期間（shift_periods）との紐付け
            $table->foreignId('shift_period_id')->constrained()->cascadeOnDelete();
            // シフト対象日
            $table->date('target_date');
            // シフトパターン（早番・遅番など）との紐付け
            $table->foreignId('shift_pattern_id')->constrained();
            
            $table->timestamps();
            
            // 同じスタッフが同じ日に重複して提出しないためのユニーク制約（推奨）
            $table->unique(['user_id', 'target_date'], 'user_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_submissions');
    }
};
