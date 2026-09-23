<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 工場設備（マシン）マスタ。
 *
 * QR コード識別子で現場から即時参照できるよう unique 制約を付与する。
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            /** 現場スキャン用の一意な QR 識別子（例: MCH-0001） */
            $table->string('qr_identifier')->unique();
            $table->string('manual_url')->nullable();
            $table->string('location')->nullable()->index();
            /** operational / down / maintenance */
            $table->string('status', 20)->default('operational')->index();
            $table->timestamps();

            $table->index(['status', 'location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
