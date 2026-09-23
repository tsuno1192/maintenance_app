<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('管理番号');
            $table->string('name')->comment('工具名');
            $table->string('category')->nullable()->comment('分類');
            $table->string('location')->nullable()->comment('保管場所');
            $table->string('status')->default('available')->comment('状態');
            $table->unsignedInteger('quantity')->default(1)->comment('在庫数');
            $table->string('manufacturer')->nullable()->comment('メーカー');
            $table->date('purchased_on')->nullable()->comment('購入日');
            $table->text('notes')->nullable()->comment('備考');
            $table->timestamps();

            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
