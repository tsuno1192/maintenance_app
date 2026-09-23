<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 申し送りに添付される画像メタデータ。
 *
 * 実ファイルは Storage ディスク（local → 将来 S3）に保存し、
 * パスとオリジナルファイル名のみを DB に保持する。
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('memo_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('memo_id')
                ->constrained('memos')
                ->cascadeOnDelete();
            /** ストレージ上の相対パス（ディスク切替に耐える設計） */
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();

            $table->index('memo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_images');
    }
};
