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
        Schema::create('repair_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trouble_id')->constrained('troubles')->cascadeOnDelete();

            // 分類
            $table->string('category_major')->comment('大分類');
            $table->string('category_middle')->nullable()->comment('中分類');
            $table->string('category_minor')->nullable()->comment('小分類');

            // 発生事象
            $table->string('title')->comment('発生事象件名');
            $table->text('content')->nullable()->comment('内容');
            $table->text('investigation_result')->nullable()->comment('調査結果');
            $table->text('cause')->nullable()->comment('発生原因');

            // 補修情報
            $table->date('repaired_on')->nullable()->comment('補修日');
            $table->text('used_spare_parts')->nullable()->comment('使用予備品');
            $table->text('used_drawings')->nullable()->comment('使用図面');

            // 動作確認記録
            $table->text('trial_run_result')->nullable()->comment('試運転結果');
            $table->text('operation_records')->nullable()->comment('各種動作記録');

            // ワークフロー状態
            // maintenance_staff -> leader -> manager -> ops_leader -> ops_manager -> completed
            $table->string('status')->default('maintenance_staff')->comment('ワークフロー状態');

            // 各承認フラグ・日時
            $table->boolean('maintenance_staff_approved')->default(false);
            $table->timestamp('maintenance_staff_approved_at')->nullable();

            $table->boolean('leader_approved')->default(false);
            $table->timestamp('leader_approved_at')->nullable();

            $table->boolean('manager_approved')->default(false);
            $table->timestamp('manager_approved_at')->nullable();

            $table->boolean('ops_leader_approved')->default(false);
            $table->timestamp('ops_leader_approved_at')->nullable();

            $table->boolean('ops_manager_approved')->default(false);
            $table->timestamp('ops_manager_approved_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('repaired_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_reports');
    }
};
