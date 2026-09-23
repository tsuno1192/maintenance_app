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
        Schema::create('support_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete(); // 児童ID
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 担当スタッフID (usersテーブル)
            $table->date('target_date'); // 対象日
            $table->text('daily_status'); // 今日の様子
            $table->text('parent_sharing')->nullable(); // 保護者向け共有事項
            $table->text('staff_handover')->nullable(); // 専門職向け申し送り
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_logs');
    }
};
