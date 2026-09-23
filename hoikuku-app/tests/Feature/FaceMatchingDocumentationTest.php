<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Documentation;
use App\Models\User;
use App\Services\ChildFaceEnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

class FaceMatchingDocumentationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 最小の JPEG バイナリ（GD 非依存）
     */
    private function jpegBytes(string $seed = 'a'): string
    {
        // 1x1 pixel JPEG
        $base = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGcP//EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAQUCf//EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQMBAT8Bf//EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQIBAT8Bf//Z');

        return $base.$seed;
    }

    public function test_auto_face_match_links_child_when_reference_matches(): void
    {
        Storage::fake('local');
        config(['face_recognition.driver' => 'fake']);

        $staff = User::factory()->create(['role' => 'staff']);
        $child = Child::create([
            'name' => 'はな',
            'birth_date' => '2021-05-01',
        ]);

        $bytes = $this->jpegBytes('hana');
        $reference = UploadedFile::fake()->createWithContent('hana-ref.jpg', $bytes);
        app(ChildFaceEnrollmentService::class)->enroll($child, $reference, true);

        $upload = UploadedFile::fake()->createWithContent('activity.jpg', $bytes);

        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => '砂場での集中',
                                'body' => 'はなちゃんは砂の感触を確かめながら遊んでいました。',
                                'skill' => '集中力',
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
        ]);

        $this->actingAs($staff)
            ->post(route('documentations.store'), [
                'photo' => $upload,
                'auto_face_match' => 1,
            ])
            ->assertRedirect(route('documentations.index'));

        $documentation = Documentation::first();
        $this->assertNotNull($documentation);
        $this->assertSame($child->id, $documentation->child_id);
        $this->assertSame(Documentation::FACE_MATCHED, $documentation->face_match_status);
        $this->assertSame('砂場での集中', $documentation->ai_episode_title);
    }

    public function test_staff_can_manually_assign_unmatched_documentation(): void
    {
        Storage::fake('local');

        $staff = User::factory()->create(['role' => 'staff']);
        $child = Child::create([
            'name' => 'たろう',
            'birth_date' => '2020-01-01',
        ]);

        $path = 'documentations/unknown.jpg';
        Storage::disk('local')->put($path, $this->jpegBytes('unknown'));

        $documentation = Documentation::create([
            'child_id' => null,
            'user_id' => $staff->id,
            'image_path' => $path,
            'face_match_status' => Documentation::FACE_UNMATCHED,
            'ai_episode_title' => '児童の確認待ち',
            'ai_body' => '顔認証または手動紐付け後にエピソードを生成します。',
        ]);

        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => '手動紐付け後の記録',
                                'body' => '本文',
                                'skill' => '探究心',
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ]),
        ]);

        $this->actingAs($staff)
            ->patch(route('admin.documentations.assign', $documentation), [
                'child_id' => $child->id,
            ])
            ->assertRedirect(route('admin.documentations.review'));

        $documentation->refresh();
        $this->assertSame($child->id, $documentation->child_id);
        $this->assertSame(Documentation::FACE_MANUAL, $documentation->face_match_status);
        $this->assertSame('手動紐付け後の記録', $documentation->ai_episode_title);
    }

    public function test_parent_cannot_view_unmatched_documentation_photo(): void
    {
        Storage::fake('local');

        $parent = User::factory()->parent()->create(['child_id' => null]);
        $staff = User::factory()->staff()->create();
        $path = 'documentations/private.jpg';
        Storage::disk('local')->put($path, $this->jpegBytes('secret'));

        $documentation = Documentation::create([
            'child_id' => null,
            'user_id' => $staff->id,
            'image_path' => $path,
            'face_match_status' => Documentation::FACE_UNMATCHED,
            'ai_episode_title' => '児童の確認待ち',
            'ai_body' => '顔認証または手動紐付け後にエピソードを生成します。',
        ]);

        $this->actingAs($parent)
            ->get(route('documentations.photo', $documentation))
            ->assertForbidden();
    }
}
