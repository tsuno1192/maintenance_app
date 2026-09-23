<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete(); // 対象の児童
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 連絡した保護者（ユーザー）
            $table->text('original_message'); // 保護者からの元のメッセージ
            $table->text('mild_message')->nullable(); // クッション機能で言い換えたメッセージ
            $table->text('ai_auto_response')->nullable(); // AIチャットボットによる一次回答
            $table->string('status')->default('pending'); // ステータス (pending: 未対応, resolved: AI解決, manual: 要スタッフ対応)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};