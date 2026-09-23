<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_face_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->string('reference_image_path');
            $table->string('external_face_id')->nullable()->index();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });

        Schema::table('documentations', function (Blueprint $table) {
            $table->foreignId('suggested_child_id')
                ->nullable()
                ->after('child_id')
                ->constrained('children')
                ->nullOnDelete();

            $table->string('face_match_status', 32)
                ->default('skipped')
                ->after('image_path')
                ->index();

            $table->decimal('face_match_confidence', 5, 2)
                ->nullable()
                ->after('face_match_status');

            $table->timestamp('face_matched_at')
                ->nullable()
                ->after('face_match_confidence');
        });

        // 自動紐付け待ちのため child_id を nullable に変更
        Schema::table('documentations', function (Blueprint $table) {
            $table->unsignedBigInteger('child_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('documentations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('suggested_child_id');
            $table->dropColumn(['face_match_status', 'face_match_confidence', 'face_matched_at']);
            $table->unsignedBigInteger('child_id')->nullable(false)->change();
        });

        Schema::dropIfExists('child_face_profiles');
    }
};
