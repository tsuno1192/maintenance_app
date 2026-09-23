<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Memo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MemoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $priority = $request->string('priority')->trim()->toString() ?: null;
        $unacked = $request->boolean('unacked');

        $memos = Memo::query()
            ->with(['user', 'machine', 'images'])
            ->withCount('images')
            ->when($priority, fn ($q) => $q->where('priority', $priority))
            ->when($unacked, fn ($q) => $q->whereNull('acknowledged_at'))
            ->latest()
            ->paginate(15);

        return response()->json($memos);
    }

    public function store(Request $request): JsonResponse
    {
        $memo = DB::transaction(function () use ($request) {
            $data = $request->except('images');
            // 認証ユーザーIDを設定（API認証している場合は $request->user()->id など）
            $data['user_id'] = $request->input('user_id', 1);

            $memo = Memo::create($data);

            // 画像の保存処理
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('memo-images/'.$memo->id, 'public');
                    $memo->images()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            return $memo;
        });

        return response()->json($memo->load('images'), 201);
    }

    public function show(Memo $memo): JsonResponse
    {
        $memo->load(['user', 'machine', 'images', 'acknowledgedByUser']);
        return response()->json($memo);
    }

    public function acknowledge(Request $request, Memo $memo): JsonResponse
    {
        if ($memo->isAcknowledged()) {
            return response()->json(['message' => '既に確認済みです。']);
        }

        $memo->update([
            'acknowledged_by' => $request->input('user_id', 1),
            'acknowledged_at' => now(),
        ]);

        return response()->json($memo);
    }

    public function destroy(Memo $memo): JsonResponse
    {
        $memo->delete();
        return response()->json(null, 204);
    }
}