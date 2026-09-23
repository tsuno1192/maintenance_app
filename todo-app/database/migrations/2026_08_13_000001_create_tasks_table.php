<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamp('due_at')->nullable();
            $table->string('source')->nullable()->comment('呼び出し元アプリ識別子（例: hoikuku-app）');
            $table->string('external_ref')->nullable()->comment('呼び出し元側の参照ID');
            $table->timestamps();

            $table->index('status');
            $table->index(['source', 'external_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
