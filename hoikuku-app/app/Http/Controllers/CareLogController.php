<?php

namespace App\Http\Controllers;

use App\Models\CareLog;
use App\Support\ChildAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $date = $request->query('date', now()->toDateString());
        $children = ChildAccess::visibleChildrenQuery($user)->get(['id', 'name']);

        $logs = CareLog::with(['child:id,name', 'user:id,name'])
            ->whereDate('logged_at', $date)
            ->when($user->isParent(), fn ($q) => $q->where('child_id', $user->child_id))
            ->latest('logged_at')
            ->paginate(50);

        return view('care.logs.index', compact('logs', 'children', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(ChildAccess::canManageCare($request->user()), 403);

        $validated = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'type' => ['required', 'in:diaper,milk,meal'],
            'diaper_status' => ['nullable', 'in:wet,dirty,both,dry'],
            'milk_ml' => ['nullable', 'integer', 'min:10', 'max:400'],
            'meal_amount' => ['nullable', 'in:all,half,little,none'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['type'] === 'diaper') {
            $request->validate(['diaper_status' => ['required', 'in:wet,dirty,both,dry']]);
        }
        if ($validated['type'] === 'milk') {
            $request->validate(['milk_ml' => ['required', 'integer', 'min:10', 'max:400']]);
        }
        if ($validated['type'] === 'meal') {
            $request->validate(['meal_amount' => ['required', 'in:all,half,little,none']]);
        }

        CareLog::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'logged_at' => now(),
        ]);

        return redirect()
            ->route('care-logs.index', ['date' => now()->toDateString()])
            ->with('success', 'ケア記録を追加しました。');
    }
}
