<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 提出期間の名称（例: 2026年4月度シフト）
            $table->dateTime('start_date'); // 提出受付開始日時
            $table->dateTime('end_date');   // 提出受付終了日時
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_periods');
    }
};