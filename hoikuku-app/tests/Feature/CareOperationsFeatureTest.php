<?php

namespace Tests\Feature;

use App\Jobs\GenerateGrowthAlbumJob;
use App\Jobs\PolishContactNoteJob;
use App\Models\Child;
use App\Models\ChildAllergy;
use App\Models\ContactNote;
use App\Models\DailyAttendance;
use App\Models\Documentation;
use App\Models\GrowthAlbum;
use App\Models\NapAlert;
use App\Models\NapCheck;
use App\Models\User;
use App\Services\AllergyGuardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CareOperationsFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $parent;

    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();

        $this->child = Child::create([
            'name' => 'はな',
            'birth_date' => '2022-01-01',
        ]);

        $this->staff = User::factory()->staff()->create();
        $this->parent = User::factory()->parent()->create([
            'child_id' => $this->child->id,
        ]);
    }

    public function test_staff_can_create_contact_note_and_dispatch_polish_job(): void
    {
        Bus::fake();

        $this->actingAs($this->staff)
            ->post(route('contact-notes.store'), [
                'child_id' => $this->child->id,
                'note_date' => now()->toDateString(),
                'raw_memo' => "- 外遊び\n- 完食",
            ])
            ->assertRedirect(route('contact-notes.index'));

        $this->assertDatabaseHas('contact_notes', [
            'child_id' => $this->child->id,
            'status' => ContactNote::STATUS_PENDING,
        ]);

        Bus::assertDispatched(PolishContactNoteJob::class);
    }

    public function test_parent_cannot_create_contact_note(): void
    {
        $this->actingAs($this->parent)
            ->post(route('contact-notes.store'), [
                'child_id' => $this->child->id,
                'note_date' => now()->toDateString(),
                'raw_memo' => 'テスト',
            ])
            ->assertForbidden();
    }

    public function test_nap_check_creates_alert_for_stomach_posture(): void
    {
        $this->actingAs($this->staff)
            ->post(route('naps.store'), [
                'child_id' => $this->child->id,
                'posture' => 'stomach',
                'breathing_status' => 'normal',
            ])
            ->assertRedirect(route('naps.index'));

        $check = NapCheck::first();
        $this->assertNotNull($check);
        $this->assertSame('warning', $check->alert_level);
        $this->assertDatabaseCount('nap_alerts', 1);
    }

    public function test_iot_sensor_endpoint_records_critical_nap_event(): void
    {
        config(['services.iot_sensor_token' => 'iot-test-token-123456']);

        $this->withToken('iot-test-token-123456')
            ->postJson('/api/nap-sensors', [
                'child_id' => $this->child->id,
                'posture' => 'back',
                'breathing_status' => 'none',
                'sensor_source' => 'crib-01',
            ])
            ->assertCreated()
            ->assertJsonPath('alert_level', 'critical');

        $this->assertDatabaseHas('nap_checks', [
            'child_id' => $this->child->id,
            'alert_level' => 'critical',
            'sensor_source' => 'crib-01',
        ]);
    }

    public function test_parent_can_upsert_own_attendance_and_temperature(): void
    {
        $this->actingAs($this->parent)
            ->post(route('attendance.upsert'), [
                'child_id' => $this->child->id,
                'attendance_date' => '2026-08-13',
                'status' => 'late',
                'temperature' => 36.7,
                'pickup_eta' => '17:30',
            ])
            ->assertRedirect();

        $row = DailyAttendance::first();
        $this->assertSame('late', $row->status);
        $this->assertSame(36.7, (float) $row->temperature);
    }

    public function test_parent_cannot_update_other_child_attendance(): void
    {
        $other = Child::create(['name' => 'たろう', 'birth_date' => '2021-01-01']);

        $this->actingAs($this->parent)
            ->post(route('attendance.upsert'), [
                'child_id' => $other->id,
                'attendance_date' => '2026-08-13',
                'status' => 'absent',
            ])
            ->assertForbidden();
    }

    public function test_growth_album_job_selects_matched_photos(): void
    {
        Documentation::create([
            'child_id' => $this->child->id,
            'user_id' => $this->staff->id,
            'image_path' => 'documentations/a.jpg',
            'face_match_status' => 'matched',
            'ai_episode_title' => '笑顔いっぱいの午後',
            'ai_body' => '本文',
            'non_cognitive_skill' => '表現力',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        $album = GrowthAlbum::create([
            'child_id' => $this->child->id,
            'year_month' => now()->format('Y-m'),
            'title' => 'テストアルバム',
            'status' => GrowthAlbum::STATUS_PENDING,
            'created_by' => $this->staff->id,
        ]);

        (new GenerateGrowthAlbumJob($album->id))->handle();

        $album->refresh();
        $this->assertSame(GrowthAlbum::STATUS_READY, $album->status);
        $this->assertGreaterThan(0, $album->items()->count());
    }

    public function test_allergy_guard_triggers_alert_on_menu_match(): void
    {
        ChildAllergy::create([
            'child_id' => $this->child->id,
            'allergen' => '卵',
            'severity' => 'critical',
            'is_active' => true,
        ]);

        $result = app(AllergyGuardService::class)->checkServing(
            $this->child,
            $this->staff,
            'lunch',
            ['卵', '小麦']
        );

        $this->assertTrue($result['alert']);
        $this->assertContains('卵', $result['matched']);
        $this->assertDatabaseHas('meal_service_checks', [
            'child_id' => $this->child->id,
            'alert_triggered' => 1,
        ]);
    }

    public function test_staff_can_create_care_log_for_diaper(): void
    {
        $this->actingAs($this->staff)
            ->post(route('care-logs.store'), [
                'child_id' => $this->child->id,
                'type' => 'diaper',
                'diaper_status' => 'wet',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('care_logs', [
            'child_id' => $this->child->id,
            'type' => 'diaper',
            'diaper_status' => 'wet',
        ]);
    }

    public function test_staff_can_acknowledge_nap_alert(): void
    {
        $check = NapCheck::create([
            'child_id' => $this->child->id,
            'user_id' => $this->staff->id,
            'checked_at' => now(),
            'posture' => 'stomach',
            'breathing_status' => 'normal',
            'alert_level' => 'warning',
        ]);

        $alert = NapAlert::create([
            'nap_check_id' => $check->id,
            'child_id' => $this->child->id,
            'message' => 'test',
        ]);

        $this->actingAs($this->staff)
            ->patch(route('naps.alerts.ack', $alert))
            ->assertRedirect();

        $this->assertNotNull($alert->fresh()->acknowledged_at);
    }
}
