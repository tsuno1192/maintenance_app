<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memo_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memo_id')->constrained('memos')->cascadeOnDelete();
            $table->string('path')->comment('保存パス');
            $table->string('original_name')->nullable()->comment('元ファイル名');
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size')->nullable()->comment('バイト数');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_images');
    }
};
