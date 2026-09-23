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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('担当ユーザー');
            $table->string('group_name')->nullable()->comment('担当Gr');
            $table->string('title')->comment('タイトル');
            $table->date('due_on')->nullable()->comment('期限');
            $table->boolean('is_completed')->default(false)->comment('完了フラグ');
            $table->foreignId('trouble_id')->nullable()->constrained('troubles')->nullOnDelete()->comment('関連トラブル');

            $table->timestamps();

            $table->index('is_completed');
            $table->index('due_on');
            $table->index('group_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
