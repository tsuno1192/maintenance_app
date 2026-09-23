<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\VoiceCommandParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = Task::query()->latest('id');

        if (in_array($status, ['pending', 'in_progress', 'done', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        $tasks = $query->paginate(30)->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'status' => $status,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
        ]);

        Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'status' => 'pending',
            'source' => 'action-list',
        ]);

        return redirect()
            ->route('tasks.index')
            ->with('status', 'タスクを作成しました。');
    }

    public function toggle(Task $task): RedirectResponse
    {
        $task->update([
            'status' => $task->status === 'done' ? 'pending' : 'done',
        ]);

        $message = $task->status === 'done'
            ? "「{$task->title}」を完了にしました。"
            : "「{$task->title}」を未完了に戻しました。";

        return redirect()
            ->route('tasks.index')
            ->with('status', $message);
    }

    public function voice(Request $request, VoiceCommandParser $parser): JsonResponse
    {
        $validated = $request->validate([
            'transcript' => ['required', 'string', 'max:500'],
            'mode' => ['sometimes', 'string', 'in:command,fill'],
        ]);

        $parsed = $parser->parse($validated['transcript']);
        $mode = $validated['mode'] ?? 'command';

        if ($mode === 'fill' || $parsed['action'] === 'unknown') {
            return response()->json([
                'status' => 'success',
                'mode' => 'fill',
                'parsed' => $parsed,
                'message' => '入力欄に反映できます。',
            ]);
        }

        if ($parsed['action'] === 'create') {
            if ($parsed['title'] === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'タスク名を聞き取れませんでした。',
                    'parsed' => $parsed,
                ], 422);
            }

            $task = Task::create([
                'title' => $parsed['title'],
                'status' => 'pending',
                'source' => 'action-list-voice',
            ]);

            return response()->json([
                'status' => 'success',
                'action' => 'create',
                'message' => "「{$task->title}」を追加しました。",
                'parsed' => $parsed,
                'task' => $task,
            ], 201);
        }

        if (in_array($parsed['action'], ['complete', 'reopen'], true)) {
            $task = $this->findTaskByTitle($parsed['title']);

            if (! $task) {
                return response()->json([
                    'status' => 'error',
                    'message' => "「{$parsed['title']}」に一致するタスクが見つかりません。",
                    'parsed' => $parsed,
                ], 404);
            }

            $task->update([
                'status' => $parsed['action'] === 'complete' ? 'done' : 'pending',
            ]);

            $label = $parsed['action'] === 'complete' ? '完了' : '未完了';

            return response()->json([
                'status' => 'success',
                'action' => $parsed['action'],
                'message' => "「{$task->title}」を{$label}にしました。",
                'parsed' => $parsed,
                'task' => $task->fresh(),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => '音声コマンドを解釈できませんでした。',
            'parsed' => $parsed,
        ], 422);
    }

    private function findTaskByTitle(string $title): ?Task
    {
        $normalized = mb_strtolower(trim($title));

        $exact = Task::query()
            ->whereRaw('LOWER(title) = ?', [$normalized])
            ->latest('id')
            ->first();

        if ($exact) {
            return $exact;
        }

        return Task::query()
            ->where('title', 'like', '%'.$title.'%')
            ->latest('id')
            ->first();
    }
}
