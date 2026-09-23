<?php

namespace App\Jobs;

use App\Models\Documentation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class GenerateDocumentationEpisodeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        public int $documentationId,
    ) {}

    public function handle(): void
    {
        $documentation = Documentation::query()->find($this->documentationId);
        if (! $documentation || ! $documentation->child_id) {
            return;
        }

        // すでに本番本文がある場合は再生成しない
        $placeholders = [
            '顔認証の確認後にエピソードを生成します。',
            '顔認証または手動紐付け後にエピソードを生成します。',
        ];

        if (filled($documentation->ai_body) && ! in_array($documentation->ai_body, $placeholders, true)) {
            return;
        }

        $path = $documentation->image_path;
        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            return;
        }

        $mime = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';
        $imageData = base64_encode(Storage::disk('local')->get($path));
        $imageUri = 'data:'.$mime.';base64,'.$imageData;

        $prompt = "この保育園の写真を見て、モンテッソーリ教育やレッジョ・エミリア・アプローチの観点から、子どもが何に夢中になり、どんな非認知能力（集中力、探究心、協調性、創造性など）が育まれているかを保護者向けに温かい文章で記述してください。
        以下のJSON形式のみで出力してください:
        {
            \"title\": \"エピソードのタイトル（例：じっくりと色を重ねて表現する喜び）\",
            \"body\": \"保護者へ向けた、子どもの姿と成長を伝える感動的なエピソード本文\",
            \"skill\": \"育まれた非認知能力のキーワード（例：集中力・表現力）\"
        }";

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            ['type' => 'image_url', 'image_url' => ['url' => $imageUri]],
                        ],
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $aiData = json_decode($result->choices[0]->message->content, true);

            $documentation->update([
                'ai_episode_title' => $aiData['title'] ?? '成長の記録',
                'ai_body' => $aiData['body'] ?? '',
                'non_cognitive_skill' => $aiData['skill'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::warning('Documentation episode generation failed', [
                'documentation_id' => $documentation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
