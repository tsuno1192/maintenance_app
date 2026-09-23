<?php

namespace App\Services;

class VoiceCommandParser
{
    /**
     * @return array{action: string, title: string, raw: string}
     */
    public function parse(string $transcript): array
    {
        $raw = trim(preg_replace('/\s+/u', ' ', $transcript) ?? '');
        $text = mb_convert_kana($raw, 'asKV', 'UTF-8');

        if ($text === '') {
            return ['action' => 'unknown', 'title' => '', 'raw' => $raw];
        }

        if (preg_match('/^(.+?)を\s*(完了(?:して|する)?|終わらせて|終わって|済みにして)(?:ください|下さい)?$/u', $text, $matches)) {
            return ['action' => 'complete', 'title' => $this->cleanTitle($matches[1]), 'raw' => $raw];
        }

        if (preg_match('/^(.+?)を\s*(未完了(?:にして|に戻して)?|戻して|再開して)(?:ください|下さい)?$/u', $text, $matches)) {
            return ['action' => 'reopen', 'title' => $this->cleanTitle($matches[1]), 'raw' => $raw];
        }

        if (preg_match('/^(.+?)を\s*(追加|登録|作成|入れて)(?:して|する)?(?:ください|下さい)?$/u', $text, $matches)) {
            return ['action' => 'create', 'title' => $this->cleanTitle($matches[1]), 'raw' => $raw];
        }

        if (preg_match('/^(?:タスク|やること|TODO|todo)[\s:：]*(.+)$/ui', $text, $matches)) {
            return ['action' => 'create', 'title' => $this->cleanTitle($matches[1]), 'raw' => $raw];
        }

        return ['action' => 'create', 'title' => $this->cleanTitle($text), 'raw' => $raw];
    }

    private function cleanTitle(string $title): string
    {
        $title = trim($title);
        $title = preg_replace('/^(タスク|やること|TODO)\s*/ui', '', $title) ?? $title;

        return trim($title, " \t\n\r\0\x0B。．、,");
    }
}
