<?php

namespace App\Services;

use App\Jobs\IndexChildFaceJob;
use App\Models\Child;
use App\Models\ChildFaceProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ChildFaceEnrollmentService
{
    public function enroll(Child $child, UploadedFile $photo, bool $isPrimary = true): ChildFaceProfile
    {
        $path = $photo->store('child-faces', 'local');

        $profile = DB::transaction(function () use ($child, $path, $isPrimary) {
            if ($isPrimary) {
                ChildFaceProfile::query()
                    ->where('child_id', $child->id)
                    ->update(['is_primary' => false]);
            }

            return ChildFaceProfile::create([
                'child_id' => $child->id,
                'reference_image_path' => $path,
                'external_face_id' => null,
                'is_primary' => $isPrimary,
            ]);
        });

        IndexChildFaceJob::dispatch($profile->id);

        return $profile;
    }

    public function delete(ChildFaceProfile $profile): void
    {
        if ($profile->external_face_id) {
            try {
                app(\App\Contracts\FaceRecognitionClient::class)->deleteFace($profile->external_face_id);
            } catch (\Throwable) {
                // 外部削除失敗でもローカルは掃除を継続
            }
        }

        if ($profile->reference_image_path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($profile->reference_image_path);
        }

        $profile->delete();
    }
}
