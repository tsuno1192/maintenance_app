<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('設備番号');
            $table->string('name')->comment('設備名');
            $table->string('area')->nullable()->comment('エリア');
            $table->string('category')->nullable()->comment('分類');
            $table->string('manufacturer')->nullable()->comment('メーカー');
            $table->string('model')->nullable()->comment('型式');
            $table->date('installed_on')->nullable()->comment('設置日');
            $table->string('status')->default('running')->comment('稼働状態');
            $table->text('notes')->nullable()->comment('備考');
            $table->timestamps();

            $table->index('area');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
