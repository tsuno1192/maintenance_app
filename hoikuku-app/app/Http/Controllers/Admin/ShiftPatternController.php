<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftPattern;
use Illuminate\Http\Request;
use App\Models\Skill;

class ShiftPatternController extends Controller
{
    /**
     * シフトパターンの一覧表示
     */
    public function index()
    {
        $shiftPatterns = ShiftPattern::all();
        return view('admin.shift_patterns.index', compact('shiftPatterns'));
    }

    /**
     * 新規作成フォームの表示
     */
    public function create()
    {
        // スキル（または資格）の一覧を取得
        $skills = Skill::all();

        // compact または with でビューに渡す
        return view('admin.shift_patterns.create', compact('skills'));
    }

    /**
     * 新規シフトパターンの保存
     */
    public function store(Request $request)
    {
        // 1. 送信データから「チェックが入っている（IDがある）もの」だけにフィルタリングする
        $skillsInput = $request->input('skills', []);
        $filteredSkills = [];

        foreach ($skillsInput as $skill) {
            // IDが存在するもの、またはチェックが入っているものだけを対象にする
            if (!empty($skill['id'])) {
                $filteredSkills[] = [
                    'id' => $skill['id'],
                    'required_count' => $skill['required_count'] ?? 1,
                ];
            }
        }

        // リクエストデータを整理したものに差し替える
        $request->merge(['skills' => $filteredSkills]);

        // 2. バリデーション
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'skills' => ['nullable', 'array'],
            'skills.*.id' => ['required', 'exists:skills,id'],
            'skills.*.required_count' => ['required', 'integer', 'min:1'],
        ]);

        // 3. シフトパターンの作成
        $shiftPattern = ShiftPattern::create([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        // 4. スキルの紐付け
        if (!empty($validated['skills'])) {
            $syncData = [];
            foreach ($validated['skills'] as $skill) {
                $syncData[$skill['id']] = ['required_count' => $skill['required_count']];
            }
            $shiftPattern->skills()->sync($syncData);
        }

        return redirect()->route('admin.shift_patterns.index')
            ->with('success', 'シフトパターンを作成しました。');
    }
    /**
     * 編集フォームの表示
     */
    public function edit(ShiftPattern $shiftPattern)
    {
        return view('admin.shift_patterns.edit', compact('shiftPattern'));
    }

    /**
     * シフトパターンの更新
     */
    public function update(Request $request, ShiftPattern $shiftPattern)
    {
        // 1. バリデーション（storeと同様）
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'skills' => ['nullable', 'array'],
            'skills.*.id' => ['required_with:skills', 'exists:skills,id'],
            'skills.*.required_count' => ['required_with:skills', 'integer', 'min:1'],
        ]);

        // 2. シフトパターン本体の基本情報を更新
        $shiftPattern->update([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        // 3. 中間テーブルのスキルと必要人数を同期（更新）
        $syncData = [];
        if (!empty($validated['skills'])) {
            foreach ($validated['skills'] as $skill) {
                // ピボットカラム（required_count）と一緒にデータをまとめる
                $syncData[$skill['id']] = ['required_count' => $skill['required_count']];
            }
        }
        // sync() を使えば、既存の紐付けを一括で更新（チェックを外したものは削除され、新しいものは追加される）
        $shiftPattern->skills()->sync($syncData);

        return redirect()->route('admin.shift_patterns.index')
            ->with('success', 'シフトパターンを更新しました。');
    }

    /**
     * シフトパターンの削除
     */
    public function destroy(ShiftPattern $shiftPattern)
    {
        $shiftPattern->delete();

        return redirect()->route('admin.shift_patterns.index')
            ->with('success', 'シフトパターンを削除しました。');
    }
}
