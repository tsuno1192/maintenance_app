<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('note_date')->index();
            $table->text('raw_memo');
            $table->text('polished_body')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('shared_at')->nullable();
            $table->timestamps();

            $table->index(['child_id', 'note_date']);
        });

        Schema::create('nap_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('checked_at')->index();
            $table->string('posture', 32)->default('unknown');
            $table->string('breathing_status', 32)->default('normal');
            $table->string('sensor_source', 64)->nullable();
            $table->string('alert_level', 16)->default('none')->index();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index(['child_id', 'checked_at']);
        });

        Schema::create('nap_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nap_check_id')->constrained()->cascadeOnDelete();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('message');
            $table->timestamp('acknowledged_at')->nullable()->index();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['child_id', 'acknowledged_at']);
        });

        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status', 32)->default('attending')->index();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->timestamp('temperature_reported_at')->nullable();
            $table->time('pickup_eta')->nullable();
            $table->string('pickup_note', 255)->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['child_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
        });

        Schema::create('growth_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('year_month', 7);
            $table->string('title');
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('shared_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['child_id', 'year_month']);
        });

        Schema::create('growth_album_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('growth_album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('documentation_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['growth_album_id', 'documentation_id']);
            $table->index(['growth_album_id', 'sort_order']);
        });

        Schema::create('care_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('logged_at')->index();
            $table->string('type', 16)->index();
            $table->string('diaper_status', 16)->nullable();
            $table->unsignedSmallInteger('milk_ml')->nullable();
            $table->string('meal_amount', 16)->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->index(['child_id', 'logged_at']);
            $table->index(['child_id', 'type', 'logged_at']);
        });

        Schema::create('child_allergies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('allergen', 100);
            $table->string('severity', 16)->default('high');
            $table->string('notes', 500)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['child_id', 'is_active']);
            $table->index('allergen');
        });

        Schema::create('meal_service_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('meal_type', 32);
            $table->json('menu_allergens');
            $table->json('matched_allergens')->nullable();
            $table->boolean('alert_triggered')->default(false)->index();
            $table->boolean('acknowledged')->default(false);
            $table->timestamp('served_at')->index();
            $table->timestamps();

            $table->index(['child_id', 'served_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_service_checks');
        Schema::dropIfExists('child_allergies');
        Schema::dropIfExists('care_logs');
        Schema::dropIfExists('growth_album_items');
        Schema::dropIfExists('growth_albums');
        Schema::dropIfExists('daily_attendances');
        Schema::dropIfExists('nap_alerts');
        Schema::dropIfExists('nap_checks');
        Schema::dropIfExists('contact_notes');
    }
};
