<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 設備に紐づく申し送り（メモ）。
 *
 * タグは AI 自動付与を見据えて JSON カラムで保持する。
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('memos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('machine_id')
                ->constrained('machines')
                ->cascadeOnDelete();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->text('message');
            $table->json('tags')->nullable();
            /** pending / in_progress / resolved */
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();

            $table->index(['machine_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memos');
    }
};
