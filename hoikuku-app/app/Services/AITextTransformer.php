<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class AITextTransformer
{
    public function makeMild(string $text): string
    {
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'あなたは保育園の連絡調整を円滑にするアシスタントです。保護者からの感情的になりがちなメッセージや要望を、保育士が受け取りやすく、客観的かつ建設的でマイルドな表現に書き換えてください。返答は変換後のテキストのみを出力してください。'
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ],
            ],
            'temperature' => 0.7,
        ]);

        return trim($result->choices[0]->message->content ?? $text);
    }
}