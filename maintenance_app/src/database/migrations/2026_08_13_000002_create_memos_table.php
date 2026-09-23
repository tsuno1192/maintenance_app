<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('作成者');
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete()->comment('関連設備');
            $table->string('title')->comment('件名');
            $table->text('body')->nullable()->comment('申し送り内容');
            $table->string('shift')->default('day')->comment('勤務帯');
            $table->string('priority')->default('normal')->comment('重要度');
            $table->string('category')->nullable()->comment('分類');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable()->comment('確認日時');
            $table->timestamps();

            $table->index('shift');
            $table->index('priority');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memos');
    }
};
