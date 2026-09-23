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
        Schema::create('individual_support_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete(); // 児童ID
            $table->text('support_goal'); // 支援目標
            $table->date('start_date'); // 開始日
            $table->date('end_date'); // 終了日
            $table->text('specific_approaches'); // 具体的な手立て・アプローチ
            $table->text('evaluation')->nullable(); // 評価
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_support_plans');
    }
};
