<?php

namespace Tests\Unit;

use App\Services\VoiceCommandParser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VoiceCommandParserTest extends TestCase
{
    #[DataProvider('transcripts')]
    public function test_parses_japanese_voice_commands(string $transcript, string $action, string $title): void
    {
        $parsed = (new VoiceCommandParser)->parse($transcript);

        $this->assertSame($action, $parsed['action']);
        $this->assertSame($title, $parsed['title']);
    }

    public static function transcripts(): array
    {
        return [
            ['牛乳を買うを追加', 'create', '牛乳を買う'],
            ['今日の会議のタスク', 'create', '今日の会議のタスク'],
            ['タスク 保護者面談', 'create', '保護者面談'],
            ['会議資料を完了して', 'complete', '会議資料'],
            ['買い物を未完了にして', 'reopen', '買い物'],
        ];
    }
}
