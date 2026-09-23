<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateDocumentationEpisodeJob;
use App\Jobs\MatchDocumentationFaceJob;
use App\Models\Child;
use App\Models\Documentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Documentation::class);

        $user = $request->user();

        if ($user->isParent()) {
            $children = Child::where('id', $user->child_id)->orderBy('name')->get();
            $documentations = Documentation::with(['child', 'user'])
                ->where('child_id', $user->child_id)
                ->whereNotNull('child_id')
                ->latest()
                ->paginate(24);
        } else {
            $children = Child::orderBy('name')->get(['id', 'name']);
            $documentations = Documentation::with(['child', 'suggestedChild', 'user'])
                ->latest()
                ->paginate(24);
        }

        return view('documentations.index', compact('children', 'documentations'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Documentation::class);

        $validated = $request->validate([
            'child_id' => 'nullable|exists:children,id',
            'photo' => [
                'required',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
            ],
            'auto_face_match' => 'sometimes|boolean',
        ]);

        $path = $request->file('photo')->store('documentations', 'local');
        $manualChildId = $validated['child_id'] ?? null;
        $autoFaceMatch = $request->boolean('auto_face_match', true);

        if ($manualChildId) {
            $status = Documentation::FACE_MANUAL;
        } elseif ($autoFaceMatch) {
            $status = Documentation::FACE_PENDING;
        } else {
            $status = Documentation::FACE_UNMATCHED;
        }

        $documentation = Documentation::create([
            'child_id' => $manualChildId,
            'suggested_child_id' => null,
            'user_id' => auth()->id(),
            'image_path' => $path,
            'face_match_status' => $status,
            'ai_episode_title' => $manualChildId ? '生成中…' : '児童の確認待ち',
            'ai_body' => $manualChildId
                ? '顔認証の確認後にエピソードを生成します。'
                : '顔認証または手動紐付け後にエピソードを生成します。',
            'non_cognitive_skill' => null,
        ]);

        if ($manualChildId) {
            // 手動指定: 顔認証をスキップしてエピソード生成へ
            GenerateDocumentationEpisodeJob::dispatch($documentation->id);
            $message = '写真を登録しました。AIエピソードを生成中です。';
        } else {
            MatchDocumentationFaceJob::dispatch($documentation->id);
            $message = '写真を登録しました。顔認証で児童を自動紐付け中です。';
        }

        return redirect()->route('documentations.index')->with('success', $message);
    }

    /**
     * 認証・所有権・パス検証付きで写真を配信する
     */
    public function photo(Request $request, Documentation $documentation): StreamedResponse
    {
        $this->authorize('view', $documentation);

        $path = $documentation->image_path;

        abort_unless(
            is_string($path)
            && str_starts_with($path, 'documentations/')
            && ! str_contains($path, '..')
            && Storage::disk('local')->exists($path),
            404
        );

        return Storage::disk('local')->response($path);
    }
}
