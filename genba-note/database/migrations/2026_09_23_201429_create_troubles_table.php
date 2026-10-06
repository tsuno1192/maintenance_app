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
        Schema::create('troubles', function (Blueprint $table) {
            $table->id(); // トラブル自体のIDは数値でOK

            // UUIDの machines.id を参照する
            $table->foreignUuid('machine_id')->constrained('machines')->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('troubles');
    }
};
