<?php

namespace App\Services\FaceRecognition;

use App\Contracts\FaceRecognitionClient;
use App\DataTransferObjects\FaceMatchResult;
use App\Models\ChildFaceProfile;
use Aws\Rekognition\RekognitionClient;
use RuntimeException;

/**
 * Amazon Rekognition による顔コレクション照合。
 * composer require aws/aws-sdk-php が必要です。
 */
class AwsRekognitionFaceRecognitionClient implements FaceRecognitionClient
{
    private RekognitionClient $client;

    private string $collectionId;

    private float $threshold;

    public function __construct(?RekognitionClient $client = null)
    {
        if (! class_exists(RekognitionClient::class)) {
            throw new RuntimeException(
                'aws/aws-sdk-php が未インストールです。composer require aws/aws-sdk-php を実行するか、FACE_RECOGNITION_DRIVER=fake を使ってください。'
            );
        }

        $this->collectionId = (string) config('face_recognition.aws.collection_id');
        $this->threshold = (float) config('face_recognition.confidence_threshold', 80);
        $this->client = $client ?? new RekognitionClient([
            'version' => 'latest',
            'region' => config('face_recognition.aws.region'),
            'credentials' => [
                'key' => config('face_recognition.aws.key'),
                'secret' => config('face_recognition.aws.secret'),
            ],
        ]);

        $this->ensureCollection();
    }

    public function indexFace(int $childId, string $absoluteImagePath): string
    {
        $bytes = $this->readBytes($absoluteImagePath);

        $result = $this->client->indexFaces([
            'CollectionId' => $this->collectionId,
            'Image' => ['Bytes' => $bytes],
            'ExternalImageId' => 'child-'.$childId,
            'DetectionAttributes' => ['DEFAULT'],
            'MaxFaces' => 1,
            'QualityFilter' => 'AUTO',
        ]);

        $faceId = $result['FaceRecords'][0]['Face']['FaceId'] ?? null;

        if (! is_string($faceId) || $faceId === '') {
            throw new RuntimeException('Rekognition が顔を検出できませんでした。別の参照写真を登録してください。');
        }

        return $faceId;
    }

    public function searchByImage(string $absoluteImagePath): ?FaceMatchResult
    {
        $bytes = $this->readBytes($absoluteImagePath);

        $result = $this->client->searchFacesByImage([
            'CollectionId' => $this->collectionId,
            'Image' => ['Bytes' => $bytes],
            'FaceMatchThreshold' => $this->threshold,
            'MaxFaces' => 1,
        ]);

        $match = $result['FaceMatches'][0] ?? null;
        if (! is_array($match)) {
            return null;
        }

        $faceId = $match['Face']['FaceId'] ?? null;
        $confidence = (float) ($match['Similarity'] ?? 0);

        if (! is_string($faceId) || $faceId === '') {
            return null;
        }

        $profile = ChildFaceProfile::query()
            ->where('external_face_id', $faceId)
            ->first();

        if (! $profile) {
            // ExternalImageId から child-{id} を復元
            $externalImageId = $match['Face']['ExternalImageId'] ?? '';
            if (preg_match('/^child-(\d+)$/', (string) $externalImageId, $m)) {
                return new FaceMatchResult(
                    childId: (int) $m[1],
                    confidence: $confidence,
                    externalFaceId: $faceId,
                );
            }

            return null;
        }

        return new FaceMatchResult(
            childId: (int) $profile->child_id,
            confidence: $confidence,
            externalFaceId: $faceId,
        );
    }

    public function deleteFace(string $externalFaceId): void
    {
        $this->client->deleteFaces([
            'CollectionId' => $this->collectionId,
            'FaceIds' => [$externalFaceId],
        ]);
    }

    private function ensureCollection(): void
    {
        try {
            $this->client->describeCollection([
                'CollectionId' => $this->collectionId,
            ]);
        } catch (\Throwable) {
            $this->client->createCollection([
                'CollectionId' => $this->collectionId,
            ]);
        }
    }

    private function readBytes(string $path): string
    {
        $bytes = @file_get_contents($path);
        if ($bytes === false || $bytes === '') {
            throw new RuntimeException("Face image is not readable: {$path}");
        }

        return $bytes;
    }
}
