<?php

namespace App\Contracts;

use App\DataTransferObjects\FaceMatchResult;

interface FaceRecognitionClient
{
    /**
     * 参照顔をコレクションへ登録し、外部 Face ID を返す。
     */
    public function indexFace(int $childId, string $absoluteImagePath): string;

    /**
     * 撮影画像から最も近い児童を検索する。該当なしは null。
     */
    public function searchByImage(string $absoluteImagePath): ?FaceMatchResult;

    /**
     * 外部 Face ID をコレクションから削除する。
     */
    public function deleteFace(string $externalFaceId): void;
}
