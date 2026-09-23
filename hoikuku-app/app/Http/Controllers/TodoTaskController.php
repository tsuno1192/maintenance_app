<?php

namespace App\Http\Controllers;

use App\Services\TodoApiClient;
use App\Services\VoiceCommandParser;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class TodoTaskController extends Controller
{
    public function index(Request $request, TodoApiClient $todoApi): View
    {
        $error = null;
        $tasks = [];
        $meta = [];

        try {
            $response = $todoApi->listTasks([
                'status' => $request->query('status'),
                'source' => $request->query('source', 'hoikuku-app'),
                'per_page' => 50,
            ]);
            $tasks = $response['data'] ?? [];
            $meta = $response['meta'] ?? [];
        } catch (RequestException $e) {
            $error = 'todo-app API エラー: HTTP '.$e->response?->status();
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('todos.index', compact('tasks', 'meta', 'error'));
    }

    public function store(Request $request, TodoApiClient $todoApi): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
        ]);

        try {
            $todoApi->createTask([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'due_at' => $validated['due_at'] ?? null,
                'status' => 'pending',
                'source' => 'hoikuku-app',
                'external_ref' => 'user:'.optional($request->user())->id,
            ]);
        } catch (RequestException $e) {
            return back()
                ->withInput()
                ->withErrors(['todo' => 'タスク作成に失敗しました（HTTP '.$e->response?->status().'）']);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['todo' => $e->getMessage()]);
        }

        return redirect()
            ->route('todos.index')
            ->with('status', 'todo-app にタスクを作成しました。');
    }

    public function toggle(Request $request, int $taskId, TodoApiClient $todoApi): RedirectResponse
    {
        try {
            $current = $todoApi->getTask($taskId);
            $task = $current['data'] ?? [];
            $next = (($task['status'] ?? '') === 'done') ? 'pending' : 'done';
            $todoApi->updateTask($taskId, ['status' => $next]);
        } catch (RequestException $e) {
            return back()->withErrors(['todo' => '状態更新に失敗しました（HTTP '.$e->response?->status().'）']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['todo' => $e->getMessage()]);
        }

        return redirect()->route('todos.index')->with('status', 'タスク状態を更新しました。');
    }

    public function voice(Request $request, TodoApiClient $todoApi, VoiceCommandParser $parser): JsonResponse
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

        try {
            if ($parsed['action'] === 'create') {
                if ($parsed['title'] === '') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'タスク名を聞き取れませんでした。',
                        'parsed' => $parsed,
                    ], 422);
                }

                $created = $todoApi->createTask([
                    'title' => $parsed['title'],
                    'status' => 'pending',
                    'source' => 'hoikuku-app',
                    'external_ref' => 'voice:user:'.optional($request->user())->id,
                ]);

                return response()->json([
                    'status' => 'success',
                    'action' => 'create',
                    'message' => "「{$parsed['title']}」を追加しました。",
                    'parsed' => $parsed,
                    'task' => $created['data'] ?? null,
                ], 201);
            }

            if (in_array($parsed['action'], ['complete', 'reopen'], true)) {
                $list = $todoApi->listTasks(['per_page' => 100]);
                $tasks = $list['data'] ?? [];
                $task = $this->findTaskByTitle($tasks, $parsed['title']);

                if (! $task) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "「{$parsed['title']}」に一致するタスクが見つかりません。",
                        'parsed' => $parsed,
                    ], 404);
                }

                $status = $parsed['action'] === 'complete' ? 'done' : 'pending';
                $updated = $todoApi->updateTask($task['id'], ['status' => $status]);
                $label = $parsed['action'] === 'complete' ? '完了' : '未完了';

                return response()->json([
                    'status' => 'success',
                    'action' => $parsed['action'],
                    'message' => "「{$task['title']}」を{$label}にしました。",
                    'parsed' => $parsed,
                    'task' => $updated['data'] ?? null,
                ]);
            }
        } catch (RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'todo-app API エラー: HTTP '.$e->response?->status(),
                'parsed' => $parsed,
            ], 502);
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'parsed' => $parsed,
            ], 503);
        }

        return response()->json([
            'status' => 'error',
            'message' => '音声コマンドを解釈できませんでした。',
            'parsed' => $parsed,
        ], 422);
    }

    /**
     * @param  array<int, array<string, mixed>>  $tasks
     * @return array<string, mixed>|null
     */
    private function findTaskByTitle(array $tasks, string $title): ?array
    {
        $normalized = mb_strtolower(trim($title));

        foreach ($tasks as $task) {
            if (mb_strtolower((string) ($task['title'] ?? '')) === $normalized) {
                return $task;
            }
        }

        foreach ($tasks as $task) {
            if (mb_stripos((string) ($task['title'] ?? ''), $title) !== false) {
                return $task;
            }
        }

        return null;
    }
}
