<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class ContactNotePolisher
{
    public function polish(string $rawMemo, string $childName): string
    {
        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'あなたは保育園の連絡帳作成アシスタントです。保育士の箇条書きメモを、保護者に伝わる丁寧で温かい文章に整えてください。事実は変えず、過度な誇張は避け、日本語の本文のみを出力してください。',
                    ],
                    [
                        'role' => 'user',
                        'content' => "児童名: {$childName}\nメモ:\n{$rawMemo}",
                    ],
                ],
                'temperature' => 0.5,
            ]);

            $text = trim((string) ($result->choices[0]->message->content ?? ''));

            return $text !== '' ? $text : $this->fallback($rawMemo, $childName);
        } catch (Throwable) {
            return $this->fallback($rawMemo, $childName);
        }
    }

    private function fallback(string $rawMemo, string $childName): string
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', $rawMemo) ?: [])
            ->map(fn ($line) => trim($line, " \t-・*"))
            ->filter()
            ->values();

        $joined = $lines->implode('、');

        return "{$childName}さんの本日の様子です。{$joined}。引き続きよろしくお願いいたします。";
    }
}
