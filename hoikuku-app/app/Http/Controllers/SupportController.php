<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\SupportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * 児童一覧・ダッシュボード表示
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isParent()) {
            $children = Child::with(['supportPlans' => fn ($q) => $q->latest('id')->limit(5)])
                ->where('id', $user->child_id)
                ->get();
        } else {
            $children = Child::with(['supportPlans' => fn ($q) => $q->latest('id')->limit(5)])
                ->orderBy('name')
                ->paginate(30);
        }

        return view('support.index', compact('children'));
    }

    /**
     * 支援ログ（日々の申し送り）の保存
     */
    public function storeLog(Request $request, Child $child)
    {
        abort_unless(Auth::user()->isStaff(), 403);

        $validated = $request->validate([
            'target_date' => 'required|date',
            'daily_status' => 'required|string|max:5000',
            'parent_sharing' => 'nullable|string|max:5000',
            'staff_handover' => 'nullable|string|max:5000',
        ]);

        $validated['child_id'] = $child->id;
        $validated['user_id'] = Auth::id();

        SupportLog::create($validated);

        return redirect()->back()->with('success', '支援ログを保存しました。');
    }
}
