<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });

        Schema::table('consultation_tickets', function (Blueprint $table) {
            $table->index('status');
            $table->index(['user_id', 'status']);
        });

        Schema::table('inquiries', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('support_logs', function (Blueprint $table) {
            $table->index(['child_id', 'target_date']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->index('status');
            $table->index('target_date');
        });

        Schema::table('child_face_profiles', function (Blueprint $table) {
            $table->index(['child_id', 'is_primary']);
        });

        Schema::table('documentations', function (Blueprint $table) {
            $table->index(['child_id', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });

        Schema::table('consultation_tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('support_logs', function (Blueprint $table) {
            $table->dropIndex(['child_id', 'target_date']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['target_date']);
        });

        Schema::table('child_face_profiles', function (Blueprint $table) {
            $table->dropIndex(['child_id', 'is_primary']);
        });

        Schema::table('documentations', function (Blueprint $table) {
            $table->dropIndex(['child_id', 'created_at']);
            $table->dropIndex(['user_id']);
        });
    }
};
