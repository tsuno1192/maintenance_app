<?php

namespace App\Http\Controllers\Web;

use App\Enums\MemoStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Memo\StoreMemoRequest;
use App\Http\Requests\Memo\UpdateMemoRequest;
use App\Models\Machine;
use App\Models\Memo;
use App\Models\MemoImage;
use App\Services\AiTaggingService;
use App\Services\ImageOptimizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * 申し送りリソースの Web コントローラ雛形。
 *
 * store 内に AI タグ付けサービスの呼び出し口（コメントアウト）を用意する。
 */
class MemoController extends Controller
{
    /**
     * @param  ImageOptimizationService  $imageOptimization  画像保存・最適化
     * @param  AiTaggingService  $aiTagging  AI タグ生成（将来連携）
     */
    public function __construct(
        private readonly ImageOptimizationService $imageOptimization,
        private readonly AiTaggingService $aiTagging,
    ) {}

    /**
     * 申し送り一覧。
     */
    public function index(Request $request): View
    {
        $memos = Memo::query()
            ->with(['machine', 'user', 'images'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('machine_id')->toString(), fn ($q, $machineId) => $q->where('machine_id', $machineId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('memos.index', [
            'memos' => $memos,
            'machines' => Machine::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * 作成フォーム。
     */
    public function create(Request $request): View
    {
        return view('memos.create', [
            'machines' => Machine::query()->orderBy('name')->get(),
            'selectedMachineId' => $request->string('machine_id')->toString() ?: null,
        ]);
    }

    /**
     * 申し送りを登録する（画像アップロード・AIタグ付け口付き）。
     */
    public function store(StoreMemoRequest $request): RedirectResponse
    {
        $memo = DB::transaction(function () use ($request) {
            $data = $request->safe()->except('images');
            $data['user_id'] = $request->user()->id;
            $data['status'] ??= MemoStatus::Pending->value;

            /*
             * ------------------------------------------------------------------
             * AI タグ付け（将来実装）
             * OpenAI 等の外部サービスで本文からタグを自動生成する呼び出し口。
             * 有効化時は下記コメントを解除し、手動 tags とマージする。
             * ------------------------------------------------------------------
             *
             * $aiTags = $this->aiTagging->generateTags($data['message']);
             * $data['tags'] = array_values(array_unique(array_merge($data['tags'] ?? [], $aiTags)));
             */

            $memo = Memo::query()->create($data);

            /** @var array<int, UploadedFile>|null $images */
            $images = $request->file('images');

            if (is_array($images)) {
                foreach ($images as $image) {
                    $path = $this->imageOptimization->storeMemoImage($image);

                    MemoImage::query()->create([
                        'memo_id' => $memo->id,
                        'file_path' => $path,
                        'original_name' => $image->getClientOriginalName(),
                    ]);
                }
            }

            return $memo;
        });

        return redirect()
            ->route('memos.show', $memo)
            ->with('status', '申し送りを投稿しました。');
    }

    /**
     * 申し送り詳細。
     */
    public function show(Memo $memo): View
    {
        $memo->load(['machine', 'user', 'images']);

        return view('memos.show', compact('memo'));
    }

    /**
     * 編集フォーム（雛形）。
     */
    public function edit(Memo $memo): View
    {
        return view('memos.edit', [
            'memo' => $memo,
            'machines' => Machine::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * 申し送りを更新する。
     */
    public function update(UpdateMemoRequest $request, Memo $memo): RedirectResponse
    {
        $memo->update($request->safe()->except('images'));

        return redirect()
            ->route('memos.show', $memo)
            ->with('status', '申し送りを更新しました。');
    }

    /**
     * 申し送りを削除する。
     */
    public function destroy(Memo $memo): RedirectResponse
    {
        $memo->load('images');

        foreach ($memo->images as $image) {
            $this->imageOptimization->delete($image->file_path);
        }

        $memo->delete();

        return redirect()
            ->route('memos.index')
            ->with('status', '申し送りを削除しました。');
    }
}
