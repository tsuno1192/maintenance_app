<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateDocumentationEpisodeJob;
use App\Models\Child;
use App\Models\Documentation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentationReviewController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('review', Documentation::class);

        $documentations = Documentation::with(['child', 'suggestedChild', 'user'])
            ->where(function ($query) {
                $query->whereNull('child_id')
                    ->orWhereIn('face_match_status', [
                        Documentation::FACE_PENDING,
                        Documentation::FACE_UNMATCHED,
                    ]);
            })
            ->latest()
            ->paginate(20);

        $children = Child::orderBy('name')->get();

        return view('admin.documentations.review', compact('documentations', 'children'));
    }

    public function update(Request $request, Documentation $documentation): RedirectResponse
    {
        $this->authorize('assignChild', $documentation);

        $validated = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
        ]);

        $needsEpisode = blank($documentation->ai_body)
            || $documentation->ai_body === '顔認証または手動紐付け後にエピソードを生成します。'
            || $documentation->ai_body === '顔認証の確認後にエピソードを生成します。'
            || $documentation->ai_episode_title === '児童の確認待ち'
            || $documentation->ai_episode_title === '生成中…';

        $documentation->update([
            'child_id' => $validated['child_id'],
            'face_match_status' => Documentation::FACE_MANUAL,
            'face_matched_at' => now(),
        ]);

        if ($needsEpisode) {
            GenerateDocumentationEpisodeJob::dispatch($documentation->id);
        }

        return redirect()
            ->route('admin.documentations.review')
            ->with('success', '児童IDを手動で紐付けました。');
    }
    
    public function destroy(Documentation $documentation): RedirectResponse
    {
        // 権限チェック（必要に応じて変更してください）
        $this->authorize('delete', $documentation);

        // 必要に応じてサーバー上の画像ファイルを削除
        // if ($documentation->image_path) {
        //     Storage::disk('public')->delete($documentation->image_path);
        // }

        // レコードを削除
        $documentation->delete();

        return redirect()
            ->back()
            ->with('success', '写真を削除しました。');
    }
}
