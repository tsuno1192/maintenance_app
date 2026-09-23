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
        Schema::create('troubles', function (Blueprint $table) {
            $table->id();

            // 分類
            $table->string('category_major')->comment('大分類（設備）');
            $table->string('category_middle')->nullable()->comment('中分類（エリア・機器）');
            $table->string('category_minor')->nullable()->comment('小分類（計器・パーツ）');

            // 件名・内容
            $table->string('title')->comment('件名');
            $table->text('content')->nullable()->comment('内容');
            $table->text('investigation')->nullable()->comment('調査内容');
            $table->text('estimated_cause')->nullable()->comment('推定原因');

            // 日付・必要資材
            $table->date('occurred_on')->nullable()->comment('発生日');
            $table->date('repair_requested_on')->nullable()->comment('補修依頼日');
            $table->text('required_spare_parts')->nullable()->comment('必要予備品');
            $table->text('required_drawings')->nullable()->comment('必要図面');

            // 作成情報
            $table->string('created_group')->nullable()->comment('作成Gr');
            $table->string('reporter_name')->nullable()->comment('報告者');
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();

            // ワークフロー状態
            // discoverer -> leader -> ops_manager -> maintenance_leader -> maintenance_manager -> completed
            $table->string('status')->default('discoverer')->comment('ワークフロー状態');

            // 各承認フラグ・日時
            $table->boolean('discoverer_approved')->default(false);
            $table->timestamp('discoverer_approved_at')->nullable();

            $table->boolean('leader_approved')->default(false);
            $table->timestamp('leader_approved_at')->nullable();

            $table->boolean('ops_manager_approved')->default(false);
            $table->timestamp('ops_manager_approved_at')->nullable();

            $table->boolean('maintenance_leader_approved')->default(false);
            $table->timestamp('maintenance_leader_approved_at')->nullable();

            $table->boolean('maintenance_manager_approved')->default(false);
            $table->timestamp('maintenance_manager_approved_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('occurred_on');
            $table->index(['category_major', 'category_middle', 'category_minor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('troubles');
    }
};
