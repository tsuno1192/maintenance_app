<?php

namespace Tests\Feature;

use App\Events\NapAlertCreated;
use App\Jobs\PolishConsultationMessageJob;
use App\Jobs\ProcessInquiryAiJob;
use App\Models\Child;
use App\Models\ConsultationMessage;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\NapMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

class QueuedAiAndRealtimeNapTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultation_store_dispatches_polish_job(): void
    {
        Bus::fake([PolishConsultationMessageJob::class]);

        $parent = User::factory()->parent()->create([
            'child_id' => Child::create(['name' => 'はな', 'birth_date' => '2022-01-01'])->id,
        ]);

        $this->actingAs($parent)
            ->post(route('consultations.store'), [
                'title' => '相談',
                'body' => '連絡が遅いです！！',
            ])
            ->assertRedirect();

        $message = ConsultationMessage::first();
        $this->assertSame('pending', $message->ai_status);
        $this->assertSame('連絡が遅いです！！', $message->original_body);

        Bus::assertDispatched(PolishConsultationMessageJob::class);
    }

    public function test_inquiry_store_dispatches_ai_job(): void
    {
        Bus::fake([ProcessInquiryAiJob::class]);

        $child = Child::create(['name' => 'はな', 'birth_date' => '2022-01-01']);
        $parent = User::factory()->parent()->create(['child_id' => $child->id]);

        $this->actingAs($parent)
            ->post(route('inquiries.store'), [
                'message' => '至急対応してください！！',
                'child_id' => $child->id,
            ])
            ->assertRedirect();

        $inquiry = Inquiry::first();
        $this->assertSame('pending', $inquiry->ai_status);
        $this->assertSame('pending', $inquiry->status);

        Bus::assertDispatched(ProcessInquiryAiJob::class);
    }

    public function test_polish_consultation_job_updates_message_body(): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [['message' => ['content' => 'お迎え時間についてご確認させてください。']]],
            ]),
        ]);

        $parent = User::factory()->parent()->create();
        $message = ConsultationMessage::create([
            'consultation_ticket_id' => \App\Models\ConsultationTicket::create([
                'user_id' => $parent->id,
                'title' => 't',
                'status' => 'open',
            ])->id,
            'user_id' => $parent->id,
            'body' => '連絡遅い！',
            'original_body' => '連絡遅い！',
            'ai_status' => 'pending',
        ]);

        (new PolishConsultationMessageJob($message->id))->handle(app(\App\Services\AITextTransformer::class));

        $message->refresh();
        $this->assertSame('done', $message->ai_status);
        $this->assertSame('お迎え時間についてご確認させてください。', $message->body);
    }

    public function test_nap_alert_broadcasts_event(): void
    {
        Event::fake([NapAlertCreated::class]);

        $child = Child::create(['name' => 'はな', 'birth_date' => '2022-01-01']);

        app(NapMonitoringService::class)->record([
            'child_id' => $child->id,
            'posture' => 'stomach',
            'breathing_status' => 'normal',
        ]);

        Event::assertDispatched(NapAlertCreated::class);
    }

    public function test_staff_can_fetch_nap_alert_feed(): void
    {
        $staff = User::factory()->staff()->create();
        $child = Child::create(['name' => 'はな', 'birth_date' => '2022-01-01']);

        app(NapMonitoringService::class)->record([
            'child_id' => $child->id,
            'posture' => 'back',
            'breathing_status' => 'none',
            'user_id' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->getJson(route('naps.alerts.feed'))
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data');
    }
}
