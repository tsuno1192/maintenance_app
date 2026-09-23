<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function index(Request $request): View
    {
        $group = $request->string('group')->trim()->toString() ?: null;

        $todos = Todo::query()
            ->with(['trouble', 'user'])
            ->when($group, fn ($q) => $q->where('group_name', $group))
            ->orderBy('is_completed')
            ->orderBy('due_on')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $groups = Todo::query()
            ->whereNotNull('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');

        return view('todos.index', compact('todos', 'groups', 'group'));
    }

    public function toggle(Todo $todo): RedirectResponse
    {
        $todo->update([
            'is_completed' => ! $todo->is_completed,
        ]);

        return back()->with('success', $todo->is_completed ? 'TO DO を完了にしました。' : 'TO DO を未完了に戻しました。');
    }
}
