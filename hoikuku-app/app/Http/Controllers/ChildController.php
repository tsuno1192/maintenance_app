<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // 💡 文字列操作用

class ChildController extends Controller
{
    public function index()
    {
        $children = Child::latest()->paginate(10);
        return view('children.index', compact('children'));
    }

    public function create()
    {
        return view('admin.children.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            /* 
             * 【将来用：手動入力に戻す場合】
             * 'child_id' => 'required|string|max:255|unique:children,child_id',
             */
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'allergies' => 'nullable|string',          
            'daily_precautions' => 'nullable|string',  
            'diagnosis' => 'nullable|string|max:255',
            'sensory_tendencies' => 'nullable|string',
            'panic_response_steps' => 'nullable|string',

// ▼ 追加：アレルギー情報（アレルゲン名とレベル）のバリデーション
            'allergen' => 'nullable|string|max:255',
            'allergy_level' => 'nullable|in:low,medium,high', // レベルの選択肢に合わせる
        ]);

        // チャイルドIDの自動生成処理
        $baseSlug = Str::slug($request->name);
        if (empty($baseSlug)) {
            $baseSlug = 'child';
        }
        $randomSymbol = ['!', '#', '@', '_'][rand(0, 3)];
        $validated['child_id'] = $baseSlug . '-' . rand(10, 99) . $randomSymbol;

        while (Child::where('child_id', $validated['child_id'])->exists()) {
            $validated['child_id'] = $baseSlug . '-' . rand(10, 99) . $randomSymbol;
        }

        // 1. 園児本体を保存
        $child = Child::create($validated);

        // 2. すでに用意されている `allergyRecords()` リレーションを使ってアレルギーを保存
        if (!empty($request->allergen)) {
            $child->allergyRecords()->create([
                'allergen' => $request->allergen,
                'level' => $request->allergy_level ?? 'low',
            ]);
        }

        return redirect()->route('admin.children.create')
            ->with('success', '園児を登録しました。自動生成されたチャイルドIDは 【 ' . $child->child_id . ' 】 です。');
    }
}