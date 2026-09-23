<?php

namespace App\Jobs;

use App\Models\Inquiry;
use App\Models\MessageCushionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class ProcessInquiryAiJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        public int $inquiryId,
    ) {}

    public function handle(): void
    {
        $inquiry = Inquiry::query()->find($this->inquiryId);
        if (! $inquiry) {
            return;
        }

        $prompt = "以下の保護者からのメッセージを分析してください。
        1. 保育園のスタッフとして、この問い合わせに対する丁寧で簡潔な一次回答を作成してください。
        2. メッセージが攻撃的、あるいは感情的な場合は、保育士が受けてもストレスを感じないよう、敬語で冷静な表現に言い換えてください（穏やかなトーンに）。
        
        メッセージ: {$inquiry->original_message}

        回答は以下のJSON形式で出力してください:
        {
            \"auto_response\": \"一次回答文\",
            \"mild_message\": \"言い換えたメッセージ\",
            \"is_emotional\": true/false
        }";

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'response_format' => ['type' => 'json_object'],
            ]);

            $aiData = json_decode($result->choices[0]->message->content, true) ?: [];

            $inquiry->update([
                'mild_message' => $aiData['mild_message'] ?? $inquiry->original_message,
                'ai_auto_response' => $aiData['auto_response'] ?? '',
                'status' => ! empty($aiData['is_emotional']) ? 'manual' : 'resolved',
                'ai_status' => 'done',
            ]);

            if (! empty($aiData['is_emotional'])) {
                MessageCushionLog::query()->firstOrCreate(
                    ['inquiry_id' => $inquiry->id],
                    [
                        'is_emotional' => true,
                        'detected_reason' => '感情的・強い表現を検知したため変換しました。',
                    ]
                );
            }
        } catch (Throwable $e) {
            $inquiry->update([
                'mild_message' => $inquiry->mild_message ?: $inquiry->original_message,
                'status' => 'manual',
                'ai_status' => 'failed',
            ]);
            throw $e;
        }
    }

    public function failed(): void
    {
        Inquiry::whereKey($this->inquiryId)->update([
            'ai_status' => 'failed',
            'status' => 'manual',
        ]);
    }
}
