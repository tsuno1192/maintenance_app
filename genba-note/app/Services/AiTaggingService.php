<?php

namespace App\Services;

/**
 * 申し送り本文からタグを自動生成する AI 連携サービスの骨組み。
 *
 * 将来的に OpenAI 等の外部 API を呼び出し、
 * 現場メモのキーワード抽出・正規化を行う想定。
 */
class AiTaggingService
{
    /**
     * テキスト内容からタグ候補を生成する。
     *
     * @param  string  $message  申し送り本文
     * @return array<int, string> 生成されたタグ一覧
     */
    public function generateTags(string $message): array
    {
        // TODO: OpenAI / 社内 LLM エンドポイントへリクエストし、タグ配列を返す
        // 例: return $this->client->chat(...);

        // 現状は空配列を返し、手動タグ or FormRequest の tags を優先する
        unset($message);

        return [];
    }
}
