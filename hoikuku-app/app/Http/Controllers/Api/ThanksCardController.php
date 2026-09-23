<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ThanksCard;
use Illuminate\Support\Facades\Auth;

class ThanksCardController extends Controller
{
    /**
     * サンクスカードの履歴取得（受信・送信一覧）
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $request->query('type', 'received'); // 'received' または 'sent'

        $query = ThanksCard::with(['sender', 'receiver', 'tags']);

        if ($type === 'sent') {
            $query->where('sender_id', $user->id);
        } else {
            $query->where('receiver_id', $user->id);
        }

        $cards = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $cards
        ]);
    }

    /**
     * サンクスカードの送信（タグ付け含む）
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:500',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id', // タグのIDが存在するか
        ]);

        $sender = Auth::user();

        // 自己宛ての送信を防ぐバリデーション（必要に応じて）
        if ($sender->id == $request->receiver_id) {
            return response()->json([
                'status' => 'error',
                'message' => '自分自身にサンクスカードを送ることはできません。'
            ], 422);
        }

        // サンクスカードの作成
        $card = ThanksCard::create([
            'sender_id' => $sender->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        // タグが選択されている場合は紐付けを行う（多対多のリレーションを想定）
        if ($request->has('tag_ids')) {
            $card->tags()->attach($request->tag_ids);
        }

        // リレーションを含めてレスポンスを返す
        $card->load(['sender', 'receiver', 'tags']);

        return response()->json([
            'status' => 'success',
            'message' => 'サンクスカードを送信しました！',
            'data' => $card
        ], 201);
    }
}
