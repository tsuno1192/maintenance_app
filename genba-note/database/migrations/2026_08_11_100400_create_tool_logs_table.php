<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 工具の貸出／返却履歴。
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tool_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tool_id')
                ->constrained('tools')
                ->cascadeOnDelete();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            /** borrowed / returned */
            $table->string('action', 20)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tool_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_logs');
    }
};
