<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->comment('貸出/返却/点検など');
            $table->unsignedInteger('quantity')->default(1);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['tool_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_logs');
    }
};
