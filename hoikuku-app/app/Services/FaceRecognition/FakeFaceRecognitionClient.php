<?php

namespace App\Services\FaceRecognition;

use App\Contracts\FaceRecognitionClient;
use App\DataTransferObjects\FaceMatchResult;
use App\Models\ChildFaceProfile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * ローカル/テスト用ドライバ。
 * 参照画像とバイト一致した場合のみマッチする（実顔認証は行わない）。
 */
class FakeFaceRecognitionClient implements FaceRecognitionClient
{
    public function indexFace(int $childId, string $absoluteImagePath): string
    {
        $this->assertReadable($absoluteImagePath);

        return 'fake-face-'.$childId.'-'.substr(hash_file('sha256', $absoluteImagePath), 0, 16);
    }

    public function searchByImage(string $absoluteImagePath): ?FaceMatchResult
    {
        $this->assertReadable($absoluteImagePath);
        $queryHash = hash_file('sha256', $absoluteImagePath);

        $profiles = ChildFaceProfile::query()
            ->whereNotNull('external_face_id')
            ->latest('id')
            ->get();

        foreach ($profiles as $profile) {
            $refPath = Storage::disk('local')->path($profile->reference_image_path);
            if (! is_readable($refPath)) {
                continue;
            }

            if (hash_file('sha256', $refPath) === $queryHash) {
                return new FaceMatchResult(
                    childId: (int) $profile->child_id,
                    confidence: 99.0,
                    externalFaceId: (string) $profile->external_face_id,
                );
            }
        }

        return null;
    }

    public function deleteFace(string $externalFaceId): void
    {
        // no-op
    }

    private function assertReadable(string $path): void
    {
        if (! is_readable($path)) {
            throw new RuntimeException("Face image is not readable: {$path}");
        }
    }
}
