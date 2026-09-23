<?php

namespace App\Jobs;

use App\Contracts\FaceRecognitionClient;
use App\Models\Documentation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MatchDocumentationFaceJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $documentationId,
    ) {}

    public function handle(FaceRecognitionClient $faces): void
    {
        $documentation = Documentation::query()->find($this->documentationId);
        if (! $documentation) {
            return;
        }

        // 手動指定済みなら顔認証をスキップ
        if ($documentation->child_id && $documentation->face_match_status === 'manual') {
            GenerateDocumentationEpisodeJob::dispatch($documentation->id);

            return;
        }

        if ($documentation->child_id && $documentation->face_match_status === 'skipped') {
            GenerateDocumentationEpisodeJob::dispatch($documentation->id);

            return;
        }

        $path = $documentation->image_path;
        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            $documentation->update([
                'face_match_status' => 'unmatched',
                'face_matched_at' => now(),
            ]);

            return;
        }

        $absolute = Storage::disk('local')->path($path);
        $threshold = (float) config('face_recognition.confidence_threshold', 80);

        try {
            $match = $faces->searchByImage($absolute);
        } catch (Throwable $e) {
            Log::warning('Face match failed', [
                'documentation_id' => $documentation->id,
                'error' => $e->getMessage(),
            ]);
            $documentation->update([
                'face_match_status' => 'unmatched',
                'face_matched_at' => now(),
            ]);

            return;
        }

        if (! $match || $match->confidence < $threshold) {
            $documentation->update([
                'suggested_child_id' => $match?->childId,
                'face_match_status' => 'unmatched',
                'face_match_confidence' => $match?->confidence,
                'face_matched_at' => now(),
            ]);

            return;
        }

        $documentation->update([
            'child_id' => $match->childId,
            'suggested_child_id' => $match->childId,
            'face_match_status' => 'matched',
            'face_match_confidence' => $match->confidence,
            'face_matched_at' => now(),
        ]);

        GenerateDocumentationEpisodeJob::dispatch($documentation->id);
    }
}
