<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ConsultationMessage;
use App\Models\ConsultationTicket;
use App\Models\Documentation;
use App\Models\IndividualSupportPlan;
use App\Models\Inquiry;
use App\Models\MessageCushionLog;
use App\Models\SupportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

/**
 * モジュール①〜④の統合フィーチャーテスト
 *
 * ① 児童プロフィール
 * ② 個別支援計画
 * ③ 支援ログ
 * ④ AI問い合わせ・クッション / AIドキュメンテーション（相談のマイルド変換含む）
 */
class HoikukuAppIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $parent;

    private Child $child;

    private IndividualSupportPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->child = Child::create([
            'name' => '山田太郎',
            'birth_date' => '2020-04-01',
            'diagnosis' => 'ASD',
            'sensory_tendencies' => '大きな音が苦手',
            'panic_response_steps' => '静かな場所へ誘導する',
        ]);

        $this->plan = IndividualSupportPlan::create([
            'child_id' => $this->child->id,
            'support_goal' => '集団活動への参加',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'specific_approaches' => '視覚支援カードを用いる',
            'evaluation' => null,
        ]);

        $this->staff = User::factory()->staff()->create([
            'name' => '保育士テスト',
            'email' => 'staff@example.com',
        ]);

        $this->parent = User::factory()->parent()->create([
            'name' => '保護者テスト',
            'email' => 'parent@example.com',
            'child_id' => $this->child->id,
        ]);
    }

    public function test_guest_cannot_access_module_routes(): void
    {
        $this->get(route('support.index'))->assertRedirect(route('login'));
        $this->get(route('consultations.create'))->assertRedirect(route('login'));
        $this->get(route('admin.consultations.index'))->assertRedirect(route('login'));
        $this->get(route('documentations.index'))->assertRedirect(route('login'));
        $this->post(route('inquiries.store'), ['message' => 'test'])->assertRedirect(route('login'));
    }

    public function test_support_dashboard_shows_child_profile_and_support_plan(): void
    {
        $response = $this->actingAs($this->staff)->get(route('support.index'));

        $response->assertOk();
        $response->assertSee('山田太郎');
        $response->assertSee('ASD');
        $response->assertSee('大きな音が苦手');
        $response->assertSee('静かな場所へ誘導する');
        $response->assertSee('集団活動への参加');
        $response->assertSee('視覚支援カードを用いる');
        $response->assertViewHas('children', function ($children) {
            return $children->contains('id', $this->child->id)
                && $children->firstWhere('id', $this->child->id)->supportPlans->contains('id', $this->plan->id);
        });
    }

    public function test_staff_can_store_support_log_for_child(): void
    {
        $payload = [
            'target_date' => '2026-08-09',
            'daily_status' => '朝の会に落ち着いて参加できました。',
            'parent_sharing' => '今日はお絵かきを楽しんでいました。',
            'staff_handover' => '午後は感覚遊びを短めに。',
        ];

        $response = $this->actingAs($this->staff)
            ->from(route('support.index'))
            ->post(route('support.logs.store', $this->child), $payload);

        $response->assertRedirect(route('support.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('support_logs', [
            'child_id' => $this->child->id,
            'user_id' => $this->staff->id,
            'target_date' => '2026-08-09',
            'daily_status' => '朝の会に落ち着いて参加できました。',
            'parent_sharing' => '今日はお絵かきを楽しんでいました。',
            'staff_handover' => '午後は感覚遊びを短めに。',
        ]);

        $this->assertSame(1, SupportLog::count());
    }

    public function test_support_log_requires_required_fields(): void
    {
        $response = $this->actingAs($this->staff)
            ->from(route('support.index'))
            ->post(route('support.logs.store', $this->child), []);

        $response->assertSessionHasErrors(['target_date', 'daily_status']);
        $this->assertDatabaseCount('support_logs', 0);
    }

    public function test_consultation_is_stored_with_ai_mild_transformation(): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'お迎え時間についてご確認させていただきたいです。本日の連絡が遅れてしまい、申し訳ありません。',
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->actingAs($this->parent)->post(route('consultations.store'), [
            'title' => 'お迎え時間について',
            'body' => '今日はどうして連絡が遅れたんですか！？',
        ]);

        $response->assertRedirect(route('consultations.create'));
        $response->assertSessionHas('success');

        $ticket = ConsultationTicket::first();
        $this->assertNotNull($ticket);
        $this->assertSame($this->parent->id, $ticket->user_id);
        $this->assertSame('お迎え時間について', $ticket->title);
        $this->assertSame('open', $ticket->status);

        $message = ConsultationMessage::first();
        $this->assertNotNull($message);
        $this->assertSame($ticket->id, $message->consultation_ticket_id);
        $this->assertSame(
            'お迎え時間についてご確認させていただきたいです。本日の連絡が遅れてしまい、申し訳ありません。',
            $message->body
        );
        $this->assertNotSame('今日はどうして連絡が遅れたんですか！？', $message->body);
    }

    public function test_staff_can_view_mild_consultation_inbox(): void
    {
        $ticket = ConsultationTicket::create([
            'user_id' => $this->parent->id,
            'title' => '給食について',
            'status' => 'open',
        ]);

        ConsultationMessage::create([
            'consultation_ticket_id' => $ticket->id,
            'user_id' => $this->parent->id,
            'body' => 'マイルド変換済みのメッセージです。',
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.consultations.index'));

        $response->assertOk();
        $response->assertSee('給食について');
        $response->assertSee('マイルド変換済みのメッセージです。');
        $response->assertSee($this->parent->name);
    }

    public function test_inquiry_cushion_saves_mild_message_and_cushion_log_when_emotional(): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'auto_response' => 'ご連絡ありがとうございます。内容を確認し、担当より折り返しご連絡いたします。',
                                'mild_message' => '本日のお迎え時間についてご確認させていただけますでしょうか。',
                                'is_emotional' => true,
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
        ]);

        $original = 'なんで連絡くれないんですか！！いい加減にしてください！';

        $response = $this->actingAs($this->parent)
            ->from(route('support.index'))
            ->post(route('inquiries.store'), [
                'message' => $original,
                'child_id' => $this->child->id,
            ]);

        $response->assertRedirect(route('support.index'));
        $response->assertSessionHas('success');

        $inquiry = Inquiry::first();
        $this->assertNotNull($inquiry);
        $this->assertSame($this->child->id, $inquiry->child_id);
        $this->assertSame($this->parent->id, $inquiry->user_id);
        $this->assertSame($original, $inquiry->original_message);
        $this->assertSame('本日のお迎え時間についてご確認させていただけますでしょうか。', $inquiry->mild_message);
        $this->assertSame(
            'ご連絡ありがとうございます。内容を確認し、担当より折り返しご連絡いたします。',
            $inquiry->ai_auto_response
        );
        $this->assertSame('manual', $inquiry->status);

        $this->assertDatabaseHas('message_cushion_logs', [
            'inquiry_id' => $inquiry->id,
            'is_emotional' => 1,
        ]);
        $this->assertSame(1, MessageCushionLog::count());
    }

    public function test_inquiry_without_emotional_tone_does_not_create_cushion_log(): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'auto_response' => 'お問い合わせありがとうございます。明日のお弁当は必要です。',
                                'mild_message' => '明日のお弁当の有無を教えてください。',
                                'is_emotional' => false,
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
        ]);

        $this->actingAs($this->parent)
            ->from(route('support.index'))
            ->post(route('inquiries.store'), [
                'message' => '明日はお弁当が必要ですか？',
                'child_id' => $this->child->id,
            ])
            ->assertRedirect(route('support.index'));

        $this->assertDatabaseHas('inquiries', [
            'status' => 'resolved',
            'original_message' => '明日はお弁当が必要ですか？',
        ]);
        $this->assertDatabaseCount('message_cushion_logs', 0);
    }

    public function test_documentation_index_lists_children_and_existing_entries(): void
    {
        Documentation::create([
            'child_id' => $this->child->id,
            'user_id' => $this->staff->id,
            'image_path' => 'documentations/sample.jpg',
            'ai_episode_title' => '色を重ねる集中の時間',
            'ai_body' => '絵の具に夢中になる姿が見られました。',
            'non_cognitive_skill' => '集中力',
        ]);

        $response = $this->actingAs($this->staff)->get(route('documentations.index'));

        $response->assertOk();
        $response->assertSee('山田太郎');
        $response->assertSee('色を重ねる集中の時間');
        $response->assertSee('集中力');
    }

    public function test_documentation_store_uses_openai_vision_and_persists_episode(): void
    {
        Storage::fake('local');

        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'ブロックで街をつくる探究心',
                                'body' => '太郎くんは友だちと役割を分けながら、丁寧に街の形を組み立てていました。',
                                'skill' => '探究心・協調性',
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
        ]);

        $photo = UploadedFile::fake()->create('activity.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->staff)
            ->from(route('documentations.index'))
            ->post(route('documentations.store'), [
                'child_id' => $this->child->id,
                'photo' => $photo,
            ]);

        $response->assertRedirect(route('documentations.index'));
        $response->assertSessionHas('success');

        $documentation = Documentation::first();
        $this->assertNotNull($documentation);
        $this->assertSame($this->child->id, $documentation->child_id);
        $this->assertSame($this->staff->id, $documentation->user_id);
        $this->assertSame(Documentation::FACE_MANUAL, $documentation->face_match_status);
        $this->assertSame('ブロックで街をつくる探究心', $documentation->ai_episode_title);
        $this->assertSame(
            '太郎くんは友だちと役割を分けながら、丁寧に街の形を組み立てていました。',
            $documentation->ai_body
        );
        $this->assertSame('探究心・協調性', $documentation->non_cognitive_skill);
        $this->assertNotEmpty($documentation->image_path);
        Storage::disk('local')->assertExists($documentation->image_path);
    }

    public function test_documentation_photo_rejects_path_traversal(): void
    {
        Storage::fake('local');

        $documentation = Documentation::create([
            'child_id' => $this->child->id,
            'user_id' => $this->staff->id,
            'image_path' => '../.env',
            'ai_episode_title' => '不正パス',
            'ai_body' => 'テスト',
            'non_cognitive_skill' => null,
        ]);

        $this->actingAs($this->staff)
            ->get(route('documentations.photo', $documentation))
            ->assertNotFound();
    }

    public function test_parent_cannot_access_staff_only_routes(): void
    {
        $this->actingAs($this->parent)
            ->post(route('support.logs.store', $this->child), [
                'target_date' => '2026-08-09',
                'daily_status' => '不正アクセス',
            ])
            ->assertForbidden();

        $this->actingAs($this->parent)
            ->get(route('admin.consultations.index'))
            ->assertForbidden();

        $this->actingAs($this->parent)
            ->post(route('documentations.store'), [
                'child_id' => $this->child->id,
                'photo' => UploadedFile::fake()->create('hack.jpg', 100, 'image/jpeg'),
            ])
            ->assertForbidden();
    }

    public function test_parent_cannot_create_inquiry_for_other_child(): void
    {
        $otherChild = Child::create([
            'name' => '他園児',
            'birth_date' => '2021-01-01',
        ]);

        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'auto_response' => 'x',
                                'mild_message' => 'y',
                                'is_emotional' => false,
                            ]),
                        ],
                    ],
                ],
            ]),
        ]);

        $this->actingAs($this->parent)
            ->post(route('inquiries.store'), [
                'message' => '他人の児童への問い合わせ',
                'child_id' => $otherChild->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_full_module_flow_from_support_to_ai_features(): void
    {
        Storage::fake('local');

        OpenAI::fake([
            // 1) 相談のマイルド変換（AITextTransformer）
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => '本日の連絡帳の記載内容について、確認させていただけますでしょうか。',
                        ],
                    ],
                ],
            ]),
            // 2) 問い合わせ・クッション
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'auto_response' => 'ご意見を承りました。担当者が確認のうえご連絡します。',
                                'mild_message' => '連絡帳の記載についてご確認をお願いできますでしょうか。',
                                'is_emotional' => true,
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
            // 3) ドキュメンテーション Vision
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => '砂場で共同する喜び',
                                'body' => '友だちと砂の城を作りながら、順番を守る姿が見られました。',
                                'skill' => '社会性・創造性',
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
        ]);

        // ①② 児童プロフィール・個別支援計画の表示
        $this->actingAs($this->staff)
            ->get(route('support.index'))
            ->assertOk()
            ->assertSee('山田太郎')
            ->assertSee('集団活動への参加');

        // ③ 支援ログ保存
        $this->actingAs($this->staff)
            ->from(route('support.index'))
            ->post(route('support.logs.store', $this->child), [
                'target_date' => '2026-08-09',
                'daily_status' => '砂場で友だちと遊びました。',
                'parent_sharing' => 'お着替えを自分でできました。',
                'staff_handover' => '明日は感覚コーナーを短く。',
            ])
            ->assertRedirect(route('support.index'));

        // ④-a 相談（マイルド変換）
        $this->actingAs($this->parent)
            ->post(route('consultations.store'), [
                'title' => '連絡帳について',
                'body' => '連絡帳、ちゃんと書いてください！！',
            ])
            ->assertRedirect(route('consultations.create'));

        // ④-b AI問い合わせ・クッション
        $this->actingAs($this->parent)
            ->from(route('support.index'))
            ->post(route('inquiries.store'), [
                'message' => '連絡帳、ちゃんと書いてください！！',
                'child_id' => $this->child->id,
            ])
            ->assertRedirect(route('support.index'));

        // ④-c AIドキュメンテーション
        $this->actingAs($this->staff)
            ->from(route('documentations.index'))
            ->post(route('documentations.store'), [
                'child_id' => $this->child->id,
                'photo' => UploadedFile::fake()->create('sandbox.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect(route('documentations.index'));

        $this->assertDatabaseCount('support_logs', 1);
        $this->assertDatabaseCount('consultation_tickets', 1);
        $this->assertDatabaseCount('consultation_messages', 1);
        $this->assertDatabaseCount('inquiries', 1);
        $this->assertDatabaseCount('message_cushion_logs', 1);
        $this->assertDatabaseCount('documentations', 1);

        $this->assertDatabaseHas('consultation_messages', [
            'body' => '本日の連絡帳の記載内容について、確認させていただけますでしょうか。',
        ]);
        $this->assertDatabaseHas('inquiries', [
            'status' => 'manual',
            'mild_message' => '連絡帳の記載についてご確認をお願いできますでしょうか。',
        ]);
        $this->assertDatabaseHas('documentations', [
            'ai_episode_title' => '砂場で共同する喜び',
            'non_cognitive_skill' => '社会性・創造性',
        ]);
    }
}
