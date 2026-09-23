<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('troubles', function (Blueprint $table) {
            $table->foreignId('machine_id')
                ->nullable()
                ->after('reporter_user_id')
                ->constrained('machines')
                ->nullOnDelete()
                ->comment('関連設備');
        });
    }

    public function down(): void
    {
        Schema::table('troubles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('machine_id');
        });
    }
};
