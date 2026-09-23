<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_cushion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_emotional')->default(false); // 感情的・強いトーンと判定されたか
            $table->text('detected_reason')->nullable(); // 判定理由や検知したキーワードなど
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_cushion_logs');
    }
};