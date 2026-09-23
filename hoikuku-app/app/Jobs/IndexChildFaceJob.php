<?php

namespace App\Jobs;

use App\Models\ChildFaceProfile;
use App\Contracts\FaceRecognitionClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class IndexChildFaceJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $faceProfileId,
    ) {}

    public function handle(FaceRecognitionClient $faces): void
    {
        $profile = ChildFaceProfile::query()->find($this->faceProfileId);
        if (! $profile) {
            return;
        }

        $path = $profile->reference_image_path;
        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            return;
        }

        try {
            $externalFaceId = $faces->indexFace(
                (int) $profile->child_id,
                Storage::disk('local')->path($path)
            );

            $profile->update(['external_face_id' => $externalFaceId]);
        } catch (Throwable $e) {
            Log::warning('Child face indexing failed', [
                'face_profile_id' => $profile->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
