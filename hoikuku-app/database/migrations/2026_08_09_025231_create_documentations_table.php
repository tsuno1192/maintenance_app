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
        Schema::create('documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete(); // 対象の児童
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 投稿した保育士
            $table->string('image_path'); // アップロードされた写真のパス
            $table->text('ai_episode_title'); // AIが生成したエピソードのタイトル（例：「ブロックの立体構造に没頭する瞬間」）
            $table->text('ai_body'); // AIが生成した保育の解説・非認知能力の成長記録文章
            $table->string('non_cognitive_skill')->nullable(); // 育まれた非認知能力（例：集中力、探究心、協調性など）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentations');
    }
};
